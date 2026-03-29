<?php

declare(strict_types=1);

namespace CA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check for Bearer token in Authorization header
        $token = $request->bearerToken();

        if ($token === null) {
            // Fall back to API key header
            $token = $request->header('X-CA-API-Key');
        }

        if ($token === null) {
            return response()->json([
                'message' => 'Unauthenticated. Provide a valid Bearer token or X-CA-API-Key header.',
            ], 401);
        }

        // Delegate to Laravel's authentication guard if available
        if (auth()->check()) {
            return $next($request);
        }

        // Verify against configured API keys if set
        $configuredKeys = config('ca.api.keys', []);

        if (!empty($configuredKeys) && !in_array($token, $configuredKeys, true)) {
            return response()->json([
                'message' => 'Invalid API credentials.',
            ], 403);
        }

        return $next($request);
    }
}
