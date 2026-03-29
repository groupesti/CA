<?php

declare(strict_types=1);

namespace CA\OCSP\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OcspContentType
{
    private const CONTENT_TYPE_REQUEST = 'application/ocsp-request';
    private const CONTENT_TYPE_RESPONSE = 'application/ocsp-response';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For POST requests, validate Content-Type
        if ($request->isMethod('POST')) {
            $contentType = $request->header('Content-Type', '');

            if (!str_contains($contentType, self::CONTENT_TYPE_REQUEST)) {
                return new \Illuminate\Http\Response('', 415, [
                    'Content-Type' => 'text/plain',
                ]);
            }
        }

        /** @var Response $response */
        $response = $next($request);

        // Set proper Content-Type on responses
        $response->headers->set('Content-Type', self::CONTENT_TYPE_RESPONSE);

        return $response;
    }
}
