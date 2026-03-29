<?php

declare(strict_types=1);

namespace CA\OCSP\Http\Controllers;

use CA\OCSP\Asn1\Maps\OCSPResponse;
use CA\OCSP\Asn1\OcspResponseBuilder;
use CA\OCSP\Contracts\OcspResponderInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class OcspController extends Controller
{
    private const CONTENT_TYPE_REQUEST = 'application/ocsp-request';
    private const CONTENT_TYPE_RESPONSE = 'application/ocsp-response';

    public function __construct(
        private readonly OcspResponderInterface $responder,
        private readonly OcspResponseBuilder $responseBuilder,
    ) {}

    /**
     * Handle an OCSP POST request (RFC 6960 Appendix A.1).
     */
    public function postRequest(Request $request, string $caUuid): Response
    {
        if (!config('ca-ocsp.enabled', true)) {
            return $this->ocspErrorResponse(OCSPResponse::STATUS_UNAUTHORIZED);
        }

        $contentType = $request->header('Content-Type', '');
        if (!str_contains($contentType, self::CONTENT_TYPE_REQUEST)) {
            return $this->ocspErrorResponse(OCSPResponse::STATUS_MALFORMED_REQUEST);
        }

        $derRequest = $request->getContent();

        if ($derRequest === '' || $derRequest === false) {
            return $this->ocspErrorResponse(OCSPResponse::STATUS_MALFORMED_REQUEST);
        }

        return $this->processOcspRequest((string) $derRequest);
    }

    /**
     * Handle an OCSP GET request (RFC 6960 Appendix A.1).
     * The request is base64url-encoded in the URL path.
     */
    public function getRequest(string $caUuid, string $encodedRequest): Response
    {
        if (!config('ca-ocsp.enabled', true)) {
            return $this->ocspErrorResponse(OCSPResponse::STATUS_UNAUTHORIZED);
        }

        try {
            // Decode base64url (RFC 4648 Section 5)
            $base64 = strtr($encodedRequest, '-_', '+/');
            $derRequest = base64_decode($base64, strict: true);

            if ($derRequest === false || $derRequest === '') {
                return $this->ocspErrorResponse(OCSPResponse::STATUS_MALFORMED_REQUEST);
            }
        } catch (\Throwable) {
            return $this->ocspErrorResponse(OCSPResponse::STATUS_MALFORMED_REQUEST);
        }

        return $this->processOcspRequest($derRequest);
    }

    /**
     * Process a DER-encoded OCSP request and return an HTTP response.
     */
    private function processOcspRequest(string $derRequest): Response
    {
        try {
            $derResponse = $this->responder->handleRequest($derRequest);
        } catch (\Throwable $e) {
            Log::error('OCSP processing error: ' . $e->getMessage());
            $derResponse = $this->responseBuilder->buildErrorResponse(OCSPResponse::STATUS_INTERNAL_ERROR);
        }

        return $this->ocspResponse($derResponse);
    }

    /**
     * Create an HTTP response with the OCSP response DER bytes.
     */
    private function ocspResponse(string $derBytes): Response
    {
        return new Response($derBytes, 200, [
            'Content-Type' => self::CONTENT_TYPE_RESPONSE,
            'Content-Length' => strlen($derBytes),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * Create an HTTP response for an OCSP error status.
     */
    private function ocspErrorResponse(int $status): Response
    {
        $derBytes = $this->responseBuilder->buildErrorResponse($status);

        return $this->ocspResponse($derBytes);
    }
}
