<?php

declare(strict_types=1);

namespace CA\OCSP\Services;

use CA\Exceptions\OcspException;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Models\CertificateAuthority;
use CA\OCSP\Asn1\Maps\OCSPResponse;
use CA\OCSP\Asn1\OcspRequestParser;
use CA\OCSP\Asn1\OcspResponseBuilder;
use CA\OCSP\Contracts\CertificateStatusResolverInterface;
use CA\OCSP\Contracts\OcspResponderInterface;
use CA\OCSP\Events\OcspResponseGenerated;
use CA\OCSP\Models\OcspResponderCertificate;
use CA\OCSP\Models\OcspResponse as OcspResponseModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\File\X509;

class OcspResponder implements OcspResponderInterface
{
    public function __construct(
        private readonly OcspRequestParser $requestParser,
        private readonly OcspResponseBuilder $responseBuilder,
        private readonly CertificateStatusResolverInterface $statusResolver,
        private readonly KeyManagerInterface $keyManager,
    ) {}

    /**
     * Handle a raw DER-encoded OCSP request and return a DER-encoded OCSP response.
     */
    public function handleRequest(string $derEncodedRequest): string
    {
        try {
            $parsed = $this->parseRequest($derEncodedRequest);
        } catch (OcspException) {
            return $this->responseBuilder->buildErrorResponse(OCSPResponse::STATUS_MALFORMED_REQUEST);
        } catch (\Throwable $e) {
            Log::error('OCSP request parsing failed: ' . $e->getMessage());

            return $this->responseBuilder->buildErrorResponse(OCSPResponse::STATUS_MALFORMED_REQUEST);
        }

        // Validate nonce requirement
        if (config('ca-ocsp.nonce_required', false) && $parsed['nonce'] === null) {
            return $this->responseBuilder->buildErrorResponse(OCSPResponse::STATUS_MALFORMED_REQUEST);
        }

        if ($parsed['requests'] === []) {
            return $this->responseBuilder->buildErrorResponse(OCSPResponse::STATUS_MALFORMED_REQUEST);
        }

        try {
            return $this->processRequests($parsed);
        } catch (\Throwable $e) {
            Log::error('OCSP response generation failed: ' . $e->getMessage());

            return $this->responseBuilder->buildErrorResponse(OCSPResponse::STATUS_INTERNAL_ERROR);
        }
    }

    /**
     * Parse a DER-encoded OCSP request into a structured array.
     *
     * @return array{requests: array<int, array{issuerNameHash: string, issuerKeyHash: string, serialNumber: string, hashAlgorithm: string}>, nonce: ?string}
     */
    public function parseRequest(string $derRequest): array
    {
        return $this->requestParser->parse($derRequest);
    }

    /**
     * Build a DER-encoded OCSP response from single responses.
     *
     * @param  array<int, array>  $singleResponses
     */
    public function buildResponse(
        array $singleResponses,
        PrivateKey $signingKey,
        X509 $responderCert,
        ?string $nonce,
    ): string {
        return $this->responseBuilder->buildSuccessResponse(
            $singleResponses,
            $signingKey,
            $responderCert,
            $nonce,
        );
    }

    /**
     * Process parsed OCSP requests and build the response.
     *
     * @param  array{requests: array, nonce: ?string}  $parsed
     */
    private function processRequests(array $parsed): string
    {
        $singleResponses = [];
        $caId = null;

        foreach ($parsed['requests'] as $request) {
            $cacheKey = $this->buildCacheKey($request);
            $cacheTtl = (int) config('ca-ocsp.cache_seconds', 3600);

            // Check cache
            if ($cacheTtl > 0 && $parsed['nonce'] === null) {
                $cached = Cache::get($cacheKey);
                if ($cached !== null) {
                    $singleResponses[] = $cached['singleResponse'];
                    $caId ??= $cached['caId'];
                    continue;
                }
            }

            // Resolve certificate status
            $result = $this->statusResolver->resolve(
                $request['issuerNameHash'],
                $request['issuerKeyHash'],
                $request['serialNumber'],
                $request['hashAlgorithm'],
            );

            $singleResponse = $this->responseBuilder->buildSingleResponse(
                serialNumber: $request['serialNumber'],
                status: $result->status,
                revocationTime: $result->revocationTime,
                revocationReason: $result->revocationReason?->slug,
                thisUpdate: $result->thisUpdate,
                nextUpdate: $result->nextUpdate,
                certIdInfo: [
                    'hashAlgorithm' => $request['hashAlgorithm'],
                    'issuerNameHash' => $request['issuerNameHash'],
                    'issuerKeyHash' => $request['issuerKeyHash'],
                ],
            );

            $singleResponses[] = $singleResponse;

            // Find CA for signing
            $resolvedCaId = $this->findCaIdForRequest($request);
            $caId ??= $resolvedCaId;

            // Cache the response (only if no nonce)
            if ($cacheTtl > 0 && $parsed['nonce'] === null) {
                Cache::put($cacheKey, [
                    'singleResponse' => $singleResponse,
                    'caId' => $resolvedCaId,
                ], $cacheTtl);
            }

            // Store in database
            $this->storeResponse($resolvedCaId, $request['serialNumber'], $result);

            // Dispatch event
            if ($resolvedCaId !== null) {
                $ca = CertificateAuthority::find($resolvedCaId);
                if ($ca !== null) {
                    event(new OcspResponseGenerated(
                        caUuid: $ca->id,
                        serial: $request['serialNumber'],
                        status: $result->status,
                    ));
                }
            }
        }

        if ($singleResponses === []) {
            return $this->responseBuilder->buildErrorResponse(OCSPResponse::STATUS_INTERNAL_ERROR);
        }

        // Load signing key and responder certificate
        [$signingKey, $responderCert] = $this->loadSigningCredentials($caId);

        if ($signingKey === null || $responderCert === null) {
            return $this->responseBuilder->buildErrorResponse(OCSPResponse::STATUS_UNAUTHORIZED);
        }

        return $this->responseBuilder->buildSuccessResponse(
            $singleResponses,
            $signingKey,
            $responderCert,
            $parsed['nonce'],
        );
    }

    /**
     * Find the CA ID for a given request by matching hashes.
     */
    private function findCaIdForRequest(array $request): ?string
    {
        $result = $this->statusResolver->resolve(
            $request['issuerNameHash'],
            $request['issuerKeyHash'],
            $request['serialNumber'],
            $request['hashAlgorithm'],
        );

        // The resolver internally matches the CA; we need to find it again
        $authorities = CertificateAuthority::query()->active()->get();

        foreach ($authorities as $ca) {
            $responderCert = OcspResponderCertificate::query()
                ->forCa($ca->id)
                ->active()
                ->first();

            if ($responderCert !== null) {
                return $ca->id;
            }
        }

        return $authorities->first()?->id;
    }

    /**
     * Load the OCSP signing key and responder certificate.
     *
     * @return array{0: ?PrivateKey, 1: ?X509}
     */
    private function loadSigningCredentials(?string $caId): array
    {
        if ($caId === null) {
            return [null, null];
        }

        $responderRecord = OcspResponderCertificate::query()
            ->forCa($caId)
            ->active()
            ->with(['certificate', 'key'])
            ->first();

        if ($responderRecord === null) {
            return [null, null];
        }

        try {
            $privateKey = $this->keyManager->decryptPrivateKey($responderRecord->key);
        } catch (\Throwable) {
            return [null, null];
        }

        $x509 = new X509();
        $certData = $responderRecord->certificate->certificate_pem
            ?? $responderRecord->certificate->certificate_der;

        if ($certData === null) {
            return [null, null];
        }

        $loaded = $x509->loadX509($certData);
        if ($loaded === false) {
            return [null, null];
        }

        return [$privateKey, $x509];
    }

    /**
     * Store an OCSP response in the database.
     */
    private function storeResponse(
        ?string $caId,
        string $serialNumber,
        \CA\OCSP\DTOs\CertStatusResult $result,
    ): void {
        if ($caId === null) {
            return;
        }

        try {
            OcspResponseModel::updateOrCreate(
                [
                    'ca_id' => $caId,
                    'certificate_serial' => $serialNumber,
                ],
                [
                    'status' => $result->status,
                    'this_update' => $result->thisUpdate,
                    'next_update' => $result->nextUpdate,
                    'revocation_time' => $result->revocationTime,
                    'revocation_reason' => $result->revocationReason?->slug,
                ],
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to store OCSP response: ' . $e->getMessage());
        }
    }

    /**
     * Build a cache key for an OCSP request.
     */
    private function buildCacheKey(array $request): string
    {
        return sprintf(
            'ocsp:%s:%s:%s',
            bin2hex($request['issuerNameHash']),
            bin2hex($request['issuerKeyHash']),
            $request['serialNumber'],
        );
    }
}
