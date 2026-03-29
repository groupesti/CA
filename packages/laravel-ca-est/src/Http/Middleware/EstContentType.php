<?php

declare(strict_types=1);

namespace CA\Est\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EST Content-Type Middleware.
 *
 * Validates request Content-Type for EST endpoints per RFC 7030
 * and ensures correct response Content-Type headers.
 */
class EstContentType
{
    /**
     * Expected Content-Types for EST POST requests.
     */
    private const array ENROLLMENT_CONTENT_TYPES = [
        'application/pkcs10',
    ];

    private const array CMC_CONTENT_TYPES = [
        'application/pkcs7-mime',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Only validate Content-Type for POST requests
        if ($request->isMethod('POST')) {
            $path = $request->path();
            $contentType = $request->header('Content-Type', '');

            // Normalize content type (strip parameters like charset)
            $normalizedType = strtolower(trim(explode(';', $contentType)[0]));

            if (str_ends_with($path, '/fullcmc')) {
                // Full CMC expects application/pkcs7-mime
                if (!in_array($normalizedType, self::CMC_CONTENT_TYPES, true)) {
                    return new \Illuminate\Http\Response(
                        'Unsupported Media Type. Expected: application/pkcs7-mime',
                        415,
                        ['Content-Type' => 'text/plain'],
                    );
                }
            } elseif (
                str_ends_with($path, '/simpleenroll')
                || str_ends_with($path, '/simplereenroll')
                || str_ends_with($path, '/serverkeygen')
            ) {
                // Enrollment endpoints expect application/pkcs10
                if (!in_array($normalizedType, self::ENROLLMENT_CONTENT_TYPES, true)) {
                    return new \Illuminate\Http\Response(
                        'Unsupported Media Type. Expected: application/pkcs10',
                        415,
                        ['Content-Type' => 'text/plain'],
                    );
                }
            }
        }

        return $next($request);
    }
}
