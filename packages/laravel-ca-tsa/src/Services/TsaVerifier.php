<?php

declare(strict_types=1);

namespace CA\Tsa\Services;

use CA\Tsa\Asn1\Maps\PKIStatusInfo;
use CA\Tsa\Asn1\Maps\TSTInfo;
use CA\Tsa\Contracts\TsaVerifierInterface;
use CA\Tsa\Events\TimestampVerified;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\EC\PublicKey as ECPublicKey;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PublicKey as RSAPublicKey;
use phpseclib3\File\ASN1;
use phpseclib3\File\X509;
use phpseclib3\Math\BigInteger;
use RuntimeException;

/**
 * Verify RFC 3161 timestamp responses.
 */
class TsaVerifier implements TsaVerifierInterface
{
    private const HASH_OIDS = [
        '1.3.14.3.2.26' => 'sha1',
        '2.16.840.1.101.3.4.2.1' => 'sha256',
        '2.16.840.1.101.3.4.2.2' => 'sha384',
        '2.16.840.1.101.3.4.2.3' => 'sha512',
        '2.16.840.1.101.3.4.2.4' => 'sha224',
    ];

    public function __construct(
        private readonly TsaRequestParser $requestParser,
    ) {}

    /**
     * Verify a TSR DER response, optionally comparing message imprint against original data.
     */
    public function verify(string $tsrDer, ?string $originalData = null): bool
    {
        try {
            $asn1 = new ASN1();
            $decoded = $asn1->decodeBER($tsrDer);

            if ($decoded === null || !isset($decoded[0])) {
                return $this->emitResult('unknown', false);
            }

            // Parse TimeStampResp outer structure
            $resp = $decoded[0];
            if (!isset($resp['content']) || count($resp['content']) < 1) {
                return $this->emitResult('unknown', false);
            }

            // Check PKIStatus
            $statusSeq = $resp['content'][0];
            $statusValue = $statusSeq['content'][0]['content'] ?? null;
            if ($statusValue === null) {
                return $this->emitResult('unknown', false);
            }

            $status = (int) (new BigInteger($statusValue, -256))->toString();
            if ($status !== PKIStatusInfo::STATUS_GRANTED && $status !== PKIStatusInfo::STATUS_GRANTED_WITH_MODS) {
                return $this->emitResult('unknown', false);
            }

            // Check timeStampToken exists
            if (count($resp['content']) < 2) {
                return $this->emitResult('unknown', false);
            }

            // Extract TSTInfo
            $tstInfo = $this->extractTstInfo($tsrDer);
            $serial = $tstInfo['serialNumber'] ?? 'unknown';

            // Verify message imprint against original data if provided
            if ($originalData !== null) {
                $hashAlgorithm = $tstInfo['hashAlgorithm'];
                $expectedHash = hash($hashAlgorithm, $originalData, false);
                if (!hash_equals($expectedHash, $tstInfo['hashedMessage'])) {
                    return $this->emitResult($serial, false);
                }
            }

            // Verify version
            if ($tstInfo['version'] !== 1) {
                return $this->emitResult($serial, false);
            }

            // Attempt signature verification if certificate is present in the response
            $signatureValid = $this->verifySignature($decoded[0], $tsrDer);

            return $this->emitResult($serial, $signatureValid);
        } catch (\Throwable) {
            return $this->emitResult('unknown', false);
        }
    }

    /**
     * Extract and decode the TSTInfo from a TSR DER response.
     */
    public function extractTstInfo(string $tsrDer): array
    {
        $asn1 = new ASN1();
        $decoded = $asn1->decodeBER($tsrDer);

        if ($decoded === null || !isset($decoded[0])) {
            throw new RuntimeException('Failed to decode timestamp response.');
        }

        $resp = $decoded[0];

        // Navigate to ContentInfo -> SignedData -> encapContentInfo -> eContent
        // TimeStampResp -> timeStampToken (ContentInfo)
        if (!isset($resp['content'][1])) {
            throw new RuntimeException('No timeStampToken in response.');
        }

        $contentInfo = $resp['content'][1];

        // ContentInfo -> [0] EXPLICIT content (SignedData)
        $signedDataWrapped = $contentInfo['content'][1] ?? null;
        if ($signedDataWrapped === null) {
            throw new RuntimeException('Missing SignedData in ContentInfo.');
        }

        // Unwrap explicit tag
        $signedDataContent = $signedDataWrapped['content'][0] ?? $signedDataWrapped;

        // SignedData -> encapContentInfo (index 2)
        $signedData = $signedDataContent['content'] ?? $signedDataContent;
        if (!is_array($signedData)) {
            throw new RuntimeException('Invalid SignedData structure.');
        }

        // Find encapContentInfo: it's the SEQUENCE after digestAlgorithms
        // version(0), digestAlgorithms(1), encapContentInfo(2), [certificates(3)], signerInfos(last)
        $encapContentInfo = null;
        foreach ($signedData as $idx => $element) {
            if ($idx >= 2 && isset($element['type']) && ($element['type'] & 0x1F) === 0x10) {
                // This is a SEQUENCE — check if it contains the TSTInfo OID
                $encapContentInfo = $element;
                break;
            }
        }

        if ($encapContentInfo === null || !isset($encapContentInfo['content'])) {
            throw new RuntimeException('Could not find encapContentInfo in SignedData.');
        }

        // encapContentInfo -> [0] EXPLICIT eContent (OCTET STRING containing TSTInfo)
        $eContentWrapped = $encapContentInfo['content'][1] ?? null;
        if ($eContentWrapped === null) {
            throw new RuntimeException('Missing eContent in encapContentInfo.');
        }

        // Navigate through explicit tag and octet string to get TSTInfo DER
        $eContent = $eContentWrapped['content'][0] ?? $eContentWrapped;
        $tstInfoDer = $eContent['content'] ?? null;

        if ($tstInfoDer === null || !is_string($tstInfoDer)) {
            // Try to extract raw bytes from the octet string
            $start = $eContent['start'] ?? 0;
            $headerLen = $eContent['headerlength'] ?? 0;
            $len = $eContent['length'] ?? 0;
            $tstInfoDer = substr($tsrDer, $start + $headerLen, $len);
        }

        // Decode TSTInfo
        $tstInfoDecoded = $asn1->decodeBER($tstInfoDer);
        if ($tstInfoDecoded === null || !isset($tstInfoDecoded[0])) {
            throw new RuntimeException('Failed to decode TSTInfo.');
        }

        $mapped = $asn1->asn1map($tstInfoDecoded[0], TSTInfo::getMap());

        if ($mapped === null || $mapped === false) {
            throw new RuntimeException('Failed to map TSTInfo structure.');
        }

        $version = isset($mapped['version']) ? (int) $mapped['version']->toString() : 0;
        $policy = $mapped['policy'] ?? '';
        $hashAlgOid = $mapped['messageImprint']['hashAlgorithm']['algorithm'] ?? '';
        $hashAlgorithm = self::HASH_OIDS[$hashAlgOid] ?? $hashAlgOid;
        $hashedMessage = isset($mapped['messageImprint']['hashedMessage'])
            ? bin2hex($mapped['messageImprint']['hashedMessage'])
            : '';
        $serialNumber = isset($mapped['serialNumber']) ? $mapped['serialNumber']->toHex() : '';
        $genTime = $mapped['genTime'] ?? '';
        $ordering = isset($mapped['ordering']) ? (bool) $mapped['ordering'] : false;
        $nonce = isset($mapped['nonce']) ? $mapped['nonce']->toString() : null;

        $accuracy = null;
        if (isset($mapped['accuracy'])) {
            $accuracy = [];
            if (isset($mapped['accuracy']['seconds'])) {
                $accuracy['seconds'] = (int) $mapped['accuracy']['seconds']->toString();
            }
            if (isset($mapped['accuracy']['millis'])) {
                $accuracy['millis'] = (int) $mapped['accuracy']['millis']->toString();
            }
            if (isset($mapped['accuracy']['micros'])) {
                $accuracy['micros'] = (int) $mapped['accuracy']['micros']->toString();
            }
        }

        return [
            'version' => $version,
            'policy' => $policy,
            'hashAlgorithm' => $hashAlgorithm,
            'hashedMessage' => $hashedMessage,
            'serialNumber' => $serialNumber,
            'genTime' => $genTime,
            'accuracy' => $accuracy,
            'ordering' => $ordering,
            'nonce' => $nonce,
        ];
    }

    /**
     * Verify the CMS signature in the SignedData structure.
     */
    private function verifySignature(array $respDecoded, string $rawDer): bool
    {
        try {
            // Navigate to SignedData
            $contentInfo = $respDecoded['content'][1] ?? null;
            if ($contentInfo === null) {
                return false;
            }

            $signedDataWrapped = $contentInfo['content'][1]['content'][0]
                ?? $contentInfo['content'][1]
                ?? null;

            if ($signedDataWrapped === null || !isset($signedDataWrapped['content'])) {
                return false;
            }

            $elements = $signedDataWrapped['content'];

            // Find certificates [0] IMPLICIT and signerInfos (last SET)
            $certificateElement = null;
            $signerInfoContent = null;

            foreach ($elements as $element) {
                $tag = $element['type'] ?? 0;

                // [0] IMPLICIT (constructed, context-specific, tag 0) = 0xA0
                if ($tag === (0x80 | 0x20 | 0)) {
                    if (isset($element['content'][0])) {
                        $certificateElement = $element['content'][0];
                    }
                }

                // SET (signerInfos)
                if (($tag & 0x1F) === 0x11 || $tag === 0x31) {
                    $signerInfoContent = $element;
                }
            }

            if ($certificateElement === null || $signerInfoContent === null) {
                return false;
            }

            // Extract the signer certificate DER from the raw response bytes
            $x509 = new X509();
            $certDer = null;

            if (isset($certificateElement['start'], $certificateElement['length'], $certificateElement['headerlength'])) {
                $certDer = substr(
                    $rawDer,
                    $certificateElement['start'],
                    $certificateElement['headerlength'] + $certificateElement['length'],
                );
            }

            if ($certDer === null || $certDer === '') {
                return false;
            }

            $certData = $x509->loadX509($certDer);
            if ($certData === false) {
                return false;
            }

            $publicKey = $x509->getPublicKey();
            if ($publicKey === null) {
                return false;
            }

            // Extract signature and signedAttrs from the first SignerInfo
            $signerInfo = $signerInfoContent['content'][0] ?? null;
            if ($signerInfo === null || !isset($signerInfo['content'])) {
                return false;
            }

            $signedAttrsDer = null;
            $signatureValue = null;
            $digestAlgOid = null;

            foreach ($signerInfo['content'] as $siElement) {
                $siTag = $siElement['type'] ?? 0;

                // signedAttrs [0] IMPLICIT
                if (isset($siElement['tag']) && $siElement['tag'] === 0
                    && isset($siElement['class']) && $siElement['class'] === ASN1::CLASS_CONTEXT_SPECIFIC) {
                    // Extract raw signedAttrs bytes from the original DER
                    if (isset($siElement['start'], $siElement['length'], $siElement['headerlength'])) {
                        $signedAttrsDer = substr(
                            $rawDer,
                            $siElement['start'],
                            $siElement['headerlength'] + $siElement['length'],
                        );
                        // Replace the context-specific tag (0xA0) with SET tag (0x31) for signature verification
                        $signedAttrsDer = "\x31" . substr($signedAttrsDer, 1);
                    }
                }

                // signature (OCTET STRING)
                if ($siTag === ASN1::TYPE_OCTET_STRING && isset($siElement['content'])) {
                    $signatureValue = $siElement['content'];
                }

                // digestAlgorithm (SEQUENCE containing OID)
                if ($siTag === ASN1::TYPE_SEQUENCE && $digestAlgOid === null) {
                    foreach ($siElement['content'] ?? [] as $algPart) {
                        if (isset($algPart['type']) && $algPart['type'] === ASN1::TYPE_OBJECT_IDENTIFIER) {
                            $digestAlgOid = $algPart['content'];
                        }
                    }
                }
            }

            if ($signedAttrsDer === null || $signatureValue === null) {
                return false;
            }

            // Determine hash algorithm
            $hashAlg = self::HASH_OIDS[$digestAlgOid] ?? 'sha256';

            // Verify signature using phpseclib
            if ($publicKey instanceof RSAPublicKey) {
                $publicKey = $publicKey->withHash($hashAlg)->withPadding(RSA::SIGNATURE_PKCS1);
                return $publicKey->verify($signedAttrsDer, $signatureValue);
            }

            if ($publicKey instanceof ECPublicKey) {
                $publicKey = $publicKey->withHash($hashAlg);
                return $publicKey->verify($signedAttrsDer, $signatureValue);
            }

            return false;
        } catch (\Throwable) {
            return false;
        }
    }

    private function emitResult(string $serial, bool $valid): bool
    {
        event(new TimestampVerified($serial, $valid));

        return $valid;
    }
}
