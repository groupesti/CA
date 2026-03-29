<?php

declare(strict_types=1);

namespace CA\Acme\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcmeContentType
{
    /**
     * Ensure Content-Type: application/jose+json for POST requests.
     * Sets response Content-Type to application/json.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('POST')) {
            $contentType = $request->header('Content-Type', '');

            if (!str_contains($contentType, 'application/jose+json')) {
                return response()->json([
                    'type' => 'urn:ietf:params:acme:error:malformed',
                    'detail' => 'Content-Type must be application/jose+json.',
                    'status' => 415,
                ], 415)
                    ->header('Content-Type', 'application/problem+json');
            }
        }

        /** @var Response $response */
        $response = $next($request);

        // Set Content-Type for JSON responses if not already set to a specific type
        $responseContentType = $response->headers->get('Content-Type', '');

        if (
            !str_contains($responseContentType, 'application/pem-certificate-chain')
            && !str_contains($responseContentType, 'application/problem+json')
        ) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
