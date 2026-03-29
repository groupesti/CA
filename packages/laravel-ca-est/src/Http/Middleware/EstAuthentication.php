<?php

declare(strict_types=1);

namespace CA\Est\Http\Middleware;

use CA\Est\Services\EstAuthenticator;
use CA\Models\CertificateAuthority;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EST Authentication Middleware.
 *
 * Handles HTTP Basic auth and/or client certificate authentication
 * for EST endpoints that require authentication per RFC 7030.
 */
class EstAuthentication
{
    public function __construct(
        private readonly EstAuthenticator $authenticator,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // /cacerts and /csrattrs do not require authentication
        $path = $request->path();
        if (str_ends_with($path, '/cacerts') || str_ends_with($path, '/csrattrs')) {
            return $next($request);
        }

        $authenticated = false;

        // Try client certificate authentication
        if (config('ca-est.allow_certificate_auth', true)) {
            $clientCertPem = $this->authenticator->getClientCertPem($request);
            if ($clientCertPem !== null) {
                // Resolve CA for certificate validation
                $ca = $this->resolveCaFromRequest($request);
                if ($ca !== null && $this->authenticator->authenticateCertificate($clientCertPem, $ca)) {
                    $authenticated = true;
                }
            }
        }

        // Try HTTP Basic authentication
        if (!$authenticated && config('ca-est.allow_basic_auth', true)) {
            $username = $request->getUser();
            $password = $request->getPassword();

            if ($username !== null && $password !== null) {
                if ($this->authenticator->authenticateBasic($username, $password)) {
                    $authenticated = true;
                }
            }
        }

        // Require client cert if configured
        if (!$authenticated && config('ca-est.require_client_cert', false)) {
            return $this->unauthorizedResponse('Certificate');
        }

        if (!$authenticated) {
            // Check if any auth method is available
            $hasBasicCredentials = $request->getUser() !== null;
            $hasClientCert = $this->authenticator->getClientCertPem($request) !== null;

            if (!$hasBasicCredentials && !$hasClientCert) {
                return $this->unauthorizedResponse('Basic');
            }

            // Credentials were provided but invalid
            return $this->unauthorizedResponse('Basic');
        }

        return $next($request);
    }

    /**
     * Build a 401 Unauthorized response with WWW-Authenticate header.
     */
    private function unauthorizedResponse(string $scheme): Response
    {
        $headers = [];

        if (config('ca-est.allow_basic_auth', true)) {
            $headers['WWW-Authenticate'] = 'Basic realm="EST"';
        }

        return new \Illuminate\Http\Response(
            'Unauthorized',
            401,
            $headers,
        );
    }

    /**
     * Resolve the CA from the route parameter.
     */
    private function resolveCaFromRequest(Request $request): ?CertificateAuthority
    {
        $label = $request->route('label');

        if ($label === null) {
            $defaultCaId = config('ca-est.ca_id');
            if ($defaultCaId !== null) {
                return CertificateAuthority::find($defaultCaId);
            }

            return null;
        }

        $ca = CertificateAuthority::find($label);

        if ($ca === null) {
            $ca = CertificateAuthority::query()
                ->where('metadata->est_label', $label)
                ->first();
        }

        return $ca;
    }
}
