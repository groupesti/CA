<?php

declare(strict_types=1);

namespace CA\Tsa\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validate request Content-Type for TSA endpoints and set response Content-Type.
 */
class TsaContentType
{
    public function handle(Request $request, Closure $next): Response
    {
        // For POST requests, validate Content-Type is application/timestamp-query
        if ($request->isMethod('POST')) {
            $contentType = $request->header('Content-Type', '');
            if (!str_contains($contentType, 'application/timestamp-query')) {
                return response('Invalid Content-Type. Expected: application/timestamp-query', 415)
                    ->header('Content-Type', 'text/plain');
            }
        }

        /** @var Response $response */
        $response = $next($request);

        // Set response Content-Type for timestamp replies
        if ($request->isMethod('POST')) {
            $response->headers->set('Content-Type', 'application/timestamp-reply');
        }

        return $response;
    }
}
