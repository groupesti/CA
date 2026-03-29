<?php

declare(strict_types=1);

namespace CA\Scep\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ScepContentType
{
    /**
     * Handle the request and ensure proper Content-Type headers on responses.
     *
     * SCEP defines specific content types:
     * - application/x-pki-message: PKI operation messages
     * - application/x-x509-ca-cert: Single CA certificate
     * - application/x-x509-ca-ra-cert: CA/RA certificate chain (PKCS#7)
     * - text/plain: GetCACaps response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only modify responses that don't already have a content type set by the controller
        if ($response->headers->has('Content-Type')) {
            return $response;
        }

        $operation = $request->query('operation', '');

        $contentType = match ($operation) {
            'GetCACert' => 'application/x-x509-ca-cert',
            'GetCACaps' => 'text/plain',
            'GetNextCACert' => 'application/x-x509-ca-ra-cert',
            'PKIOperation' => 'application/x-pki-message',
            default => $request->isMethod('POST')
                ? 'application/x-pki-message'
                : 'application/octet-stream',
        };

        $response->headers->set('Content-Type', $contentType);

        return $response;
    }
}
