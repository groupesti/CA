<?php

declare(strict_types=1);

namespace CA\Tsa\Services;

use CA\Key\Contracts\KeyManagerInterface;
use CA\Tsa\Asn1\Maps\PKIStatusInfo;
use CA\Tsa\Contracts\TsaServerInterface;
use CA\Tsa\Events\TimestampCreated;
use CA\Tsa\Models\TimestampToken;
use CA\Tsa\Models\TsaCertificate;
use RuntimeException;

/**
 * RFC 3161 Time-Stamp Authority server implementation.
 */
class TsaServer implements TsaServerInterface
{
    public function __construct(
        private readonly TsaRequestParser $requestParser,
        private readonly TsaResponseBuilder $responseBuilder,
        private readonly TsaSerialGenerator $serialGenerator,
        private readonly KeyManagerInterface $keyManager,
    ) {}

    /**
     * Handle a raw DER-encoded TimeStampReq and return a DER-encoded TimeStampResp.
     */
    public function handleRequest(string $tsqDer): string
    {
        try {
            $request = $this->parseRequest($tsqDer);

            return $this->createTimestamp(
                hashAlgorithm: $request['hashAlgorithm'],
                hashedMessage: $request['hashedMessage'],
                nonce: $request['nonce'],
                certReq: $request['certReq'],
                policyOid: $request['policyOid'],
            );
        } catch (RuntimeException $e) {
            return $this->responseBuilder->buildErrorResponse(
                PKIStatusInfo::STATUS_REJECTION,
                $e->getMessage(),
                PKIStatusInfo::FAIL_BAD_REQUEST,
            );
        } catch (\Throwable $e) {
            return $this->responseBuilder->buildErrorResponse(
                PKIStatusInfo::STATUS_REJECTION,
                'Internal server error.',
                PKIStatusInfo::FAIL_SYSTEM_FAILURE,
            );
        }
    }

    /**
     * Parse a DER-encoded TimeStampReq into its component fields.
     */
    public function parseRequest(string $tsqDer): array
    {
        return $this->requestParser->parse($tsqDer);
    }

    /**
     * Create a timestamp for the given parameters, returning DER-encoded TimeStampResp.
     */
    public function createTimestamp(
        string $hashAlgorithm,
        string $hashedMessage,
        ?string $nonce,
        bool $certReq,
        ?string $policyOid,
    ): string {
        // Resolve the active TSA certificate
        $caId = config('ca-tsa.ca_id');
        if ($caId === null) {
            throw new RuntimeException('No TSA CA configured. Set CA_TSA_CA_ID in your environment.');
        }

        $tsaCert = TsaCertificate::query()
            ->where('ca_id', $caId)
            ->where('is_active', true)
            ->with(['certificate', 'key'])
            ->first();

        if ($tsaCert === null) {
            throw new RuntimeException('No active TSA signing certificate found. Run ca:tsa:setup first.');
        }

        // Check nonce requirement
        if (config('ca-tsa.nonce_required', false) && $nonce === null) {
            throw new RuntimeException('A nonce is required by this TSA.');
        }

        // Determine policy OID
        $policyOid = $policyOid ?? config('ca-tsa.policy_oid', '1.2.3.4.1');

        // Get hash algorithm OID
        $hashAlgorithmOid = $this->requestParser->getOidForAlgorithm($hashAlgorithm);
        if ($hashAlgorithmOid === null) {
            throw new RuntimeException("Unsupported hash algorithm: {$hashAlgorithm}");
        }

        // Generate serial number
        $serialNumberHex = $this->serialGenerator->generate();

        // Record generation time with sub-second precision
        $genTime = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        // Build accuracy
        $accuracy = null;
        $accSeconds = (int) config('ca-tsa.accuracy_seconds', 1);
        $accMillis = (int) config('ca-tsa.accuracy_millis', 0);
        $accMicros = (int) config('ca-tsa.accuracy_micros', 0);
        if ($accSeconds > 0 || $accMillis > 0 || $accMicros > 0) {
            $accuracy = [
                'seconds' => $accSeconds,
                'millis' => $accMillis,
                'micros' => $accMicros,
            ];
        }

        $ordering = (bool) config('ca-tsa.ordering', false);
        $hashedMessageRaw = hex2bin($hashedMessage);

        // Build TSTInfo DER
        $tstInfoDer = $this->responseBuilder->buildTstInfo(
            policyOid: $policyOid,
            hashAlgorithmOid: $hashAlgorithmOid,
            hashedMessageRaw: $hashedMessageRaw,
            serialNumberHex: $serialNumberHex,
            genTime: $genTime,
            accuracy: $accuracy,
            ordering: $ordering,
            nonce: $nonce,
        );

        // Decrypt the signing private key
        $privateKey = $this->keyManager->decryptPrivateKey($tsaCert->key);

        // Get certificate DER
        $certificateDer = $tsaCert->certificate->certificate_der;
        if (is_string($certificateDer) && str_starts_with($certificateDer, '-----BEGIN')) {
            // PEM to DER conversion
            $pem = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\r"], '', $certificateDer);
            $certificateDer = base64_decode($pem, true);
        }

        // Determine whether to include TSA certificate in response
        $includeCert = $certReq || (bool) config('ca-tsa.include_tsa_cert', true);

        // Build the complete TimeStampResp with CMS SignedData wrapping
        $tsrDer = $this->responseBuilder->buildGrantedResponse(
            tstInfoDer: $tstInfoDer,
            privateKey: $privateKey,
            certificateDer: $certificateDer,
            includeCert: $includeCert,
            hashAlgorithm: $hashAlgorithm,
        );

        // Store the timestamp token in the database
        $token = TimestampToken::create([
            'ca_id' => $caId,
            'serial_number' => $serialNumberHex,
            'hash_algorithm' => $hashAlgorithm,
            'message_imprint' => $hashedMessage,
            'policy_oid' => $policyOid,
            'gen_time' => $genTime,
            'nonce' => $nonce,
            'accuracy' => $accuracy,
            'tsr_der' => $tsrDer,
            'signing_certificate_id' => $tsaCert->certificate_id,
            'ip_address' => request()?->ip(),
        ]);

        event(new TimestampCreated($token));

        return $tsrDer;
    }
}
