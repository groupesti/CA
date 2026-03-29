<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1;

use CA\Exceptions\OcspException;
use CA\OCSP\Asn1\Maps\BasicOCSPResponse;
use CA\OCSP\Asn1\Maps\CertID;
use CA\OCSP\Asn1\Maps\CertStatus;
use CA\OCSP\Asn1\Maps\OCSPResponse;
use CA\OCSP\Asn1\Maps\ResponseData;
use CA\OCSP\Asn1\Maps\SingleResponse as SingleResponseMap;
use Carbon\Carbon;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\ASN1;
use phpseclib3\File\ASN1\Element;
use phpseclib3\File\X509;

class OcspResponseBuilder
{
    /**
     * OID for basic OCSP response type.
     */
    private const OID_BASIC_OCSP_RESPONSE = '1.3.6.1.5.5.7.48.1.1';

    /**
     * OID for the OCSP nonce extension.
     */
    private const OID_OCSP_NONCE = '1.3.6.1.5.5.7.48.1.2';

    /**
     * Signature algorithm OIDs.
     *
     * @var array<string, string>
     */
    private const SIGNATURE_ALGORITHM_OIDS = [
        'sha256WithRSAEncryption' => '1.2.840.113549.1.1.11',
        'sha384WithRSAEncryption' => '1.2.840.113549.1.1.12',
        'sha512WithRSAEncryption' => '1.2.840.113549.1.1.13',
        'ecdsa-with-SHA256' => '1.2.840.10045.4.3.2',
        'ecdsa-with-SHA384' => '1.2.840.10045.4.3.3',
        'ecdsa-with-SHA512' => '1.2.840.10045.4.3.4',
    ];

    /**
     * Hash algorithm OIDs.
     *
     * @var array<string, string>
     */
    private const HASH_ALGORITHM_OIDS = [
        'sha1' => '1.3.14.3.2.26',
        'sha256' => '2.16.840.1.101.3.4.2.1',
        'sha384' => '2.16.840.1.101.3.4.2.2',
        'sha512' => '2.16.840.1.101.3.4.2.3',
    ];

    /**
     * Build a successful OCSP response.
     *
     * @param  array<int, array>  $singleResponses
     *
     * @throws OcspException
     */
    public function buildSuccessResponse(
        array $singleResponses,
        PrivateKey $signingKey,
        X509 $responderCert,
        ?string $nonce,
    ): string {
        $producedAt = Carbon::now('UTC');

        // Build responder ID (byName)
        $responderDn = $responderCert->getDN(X509::DN_DER);
        if ($responderDn === false) {
            throw new OcspException('Failed to extract responder DN.');
        }

        // Build response extensions (nonce)
        $responseExtensions = [];
        if ($nonce !== null) {
            $nonceValue = ASN1::encodeDER(
                $nonce,
                ['type' => ASN1::TYPE_OCTET_STRING],
            );
            $responseExtensions[] = [
                'extnId' => self::OID_OCSP_NONCE,
                'critical' => false,
                'extnValue' => $nonceValue,
            ];
        }

        // Build ResponseData
        $responseData = [
            'responderID' => [
                'byName' => new Element($responderDn),
            ],
            'producedAt' => $producedAt->format('YmdHis') . 'Z',
            'responses' => $singleResponses,
        ];

        if ($responseExtensions !== []) {
            $responseData['responseExtensions'] = $responseExtensions;
        }

        ASN1::loadOIDs(array_merge(
            self::SIGNATURE_ALGORITHM_OIDS,
            self::HASH_ALGORITHM_OIDS,
            [self::OID_OCSP_NONCE => 'id-pkix-ocsp-nonce'],
        ));

        // Encode tbsResponseData
        $tbsResponseDataDer = ASN1::encodeDER($responseData, ResponseData::getMap());

        if ($tbsResponseDataDer === '') {
            throw new OcspException('Failed to encode tbsResponseData.');
        }

        // Sign the tbsResponseData
        $signatureAlgOid = $this->getSignatureAlgorithmOid($signingKey);
        $signature = $this->signData($tbsResponseDataDer, $signingKey);

        // Get responder certificate DER
        $responderCertDer = $responderCert->saveX509($responderCert->getCurrentCert());

        // Build BasicOCSPResponse
        $basicResponse = [
            'tbsResponseData' => $responseData,
            'signatureAlgorithm' => [
                'algorithm' => $signatureAlgOid,
            ],
            'signature' => $signature,
        ];

        if ($responderCertDer !== false && $responderCertDer !== '') {
            $basicResponse['certs'] = [new Element($responderCertDer)];
        }

        $basicResponseDer = ASN1::encodeDER($basicResponse, BasicOCSPResponse::getMap());

        if ($basicResponseDer === '') {
            throw new OcspException('Failed to encode BasicOCSPResponse.');
        }

        // Build final OCSPResponse
        return $this->wrapInOcspResponse(OCSPResponse::STATUS_SUCCESSFUL, $basicResponseDer);
    }

    /**
     * Build an error OCSP response (no responseBytes).
     */
    public function buildErrorResponse(int $status): string
    {
        return $this->wrapInOcspResponse($status, null);
    }

    /**
     * Build a SingleResponse array structure.
     *
     * @param  array{hashAlgorithm: string, issuerNameHash: string, issuerKeyHash: string}  $certIdInfo
     */
    public function buildSingleResponse(
        string $serialNumber,
        string $status,
        ?Carbon $revocationTime,
        ?int $revocationReason,
        Carbon $thisUpdate,
        ?Carbon $nextUpdate,
        array $certIdInfo = [],
    ): array {
        $hashAlgOid = self::HASH_ALGORITHM_OIDS[$certIdInfo['hashAlgorithm'] ?? 'sha1'] ?? '1.3.14.3.2.26';

        $certId = [
            'hashAlgorithm' => [
                'algorithm' => $hashAlgOid,
            ],
            'issuerNameHash' => $certIdInfo['issuerNameHash'] ?? '',
            'issuerKeyHash' => $certIdInfo['issuerKeyHash'] ?? '',
            'serialNumber' => $serialNumber,
        ];

        $certStatus = match ($status) {
            'good' => ['good' => ''],
            'revoked' => [
                'revoked' => array_filter([
                    'revocationTime' => $revocationTime?->format('YmdHis') . 'Z',
                    'revocationReason' => $revocationReason,
                ], fn ($v) => $v !== null),
            ],
            default => ['unknown' => ''],
        };

        $singleResponse = [
            'certID' => $certId,
            'certStatus' => $certStatus,
            'thisUpdate' => $thisUpdate->format('YmdHis') . 'Z',
        ];

        if ($nextUpdate !== null) {
            $singleResponse['nextUpdate'] = $nextUpdate->format('YmdHis') . 'Z';
        }

        return $singleResponse;
    }

    /**
     * Wrap a BasicOCSPResponse (or null) in an OCSPResponse envelope.
     */
    private function wrapInOcspResponse(int $status, ?string $basicResponseDer): string
    {
        ASN1::loadOIDs([
            self::OID_BASIC_OCSP_RESPONSE => 'id-pkix-ocsp-basic',
        ]);

        $statusMapping = [
            0 => 'successful',
            1 => 'malformedRequest',
            2 => 'internalError',
            3 => 'tryLater',
            5 => 'sigRequired',
            6 => 'unauthorized',
        ];

        $response = [
            'responseStatus' => $statusMapping[$status] ?? 'internalError',
        ];

        if ($basicResponseDer !== null) {
            $response['responseBytes'] = [
                'responseType' => self::OID_BASIC_OCSP_RESPONSE,
                'response' => $basicResponseDer,
            ];
        }

        $der = ASN1::encodeDER($response, OCSPResponse::getMap());

        if ($der === '') {
            // Fallback: manually build a minimal error response
            return $this->buildMinimalErrorResponse($status);
        }

        return $der;
    }

    /**
     * Build a minimal DER-encoded error response as fallback.
     */
    private function buildMinimalErrorResponse(int $status): string
    {
        // SEQUENCE { ENUMERATED { status } }
        $enumerated = chr(0x0A) . chr(0x01) . chr($status);
        $sequence = chr(0x30) . chr(strlen($enumerated)) . $enumerated;

        return $sequence;
    }

    /**
     * Determine the signature algorithm OID based on the key type.
     */
    private function getSignatureAlgorithmOid(PrivateKey $key): string
    {
        if ($key instanceof RSA\PrivateKey || $key instanceof RSA) {
            return self::SIGNATURE_ALGORITHM_OIDS['sha256WithRSAEncryption'];
        }

        if ($key instanceof EC\PrivateKey || $key instanceof EC) {
            return self::SIGNATURE_ALGORITHM_OIDS['ecdsa-with-SHA256'];
        }

        return self::SIGNATURE_ALGORITHM_OIDS['sha256WithRSAEncryption'];
    }

    /**
     * Sign data with the given private key.
     *
     * @throws OcspException
     */
    private function signData(string $data, PrivateKey $key): string
    {
        try {
            if ($key instanceof RSA\PrivateKey || $key instanceof RSA) {
                $signer = $key->withPadding(RSA::SIGNATURE_PKCS1)->withHash('sha256');

                return $signer->sign($data);
            }

            if ($key instanceof EC\PrivateKey || $key instanceof EC) {
                $signer = $key->withHash('sha256');

                return $signer->sign($data);
            }

            return $key->sign($data);
        } catch (\Throwable $e) {
            throw new OcspException('Failed to sign OCSP response: ' . $e->getMessage(), 0, $e);
        }
    }
}
