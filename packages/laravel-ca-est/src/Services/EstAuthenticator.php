<?php

declare(strict_types=1);

namespace CA\Est\Services;

use CA\Crt\Models\Certificate;
use CA\Models\CertificateStatus;
use CA\Models\CertificateAuthority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use phpseclib3\File\X509;

/**
 * Handles EST authentication methods per RFC 7030.
 *
 * Supports HTTP Basic authentication and TLS client certificate authentication.
 */
class EstAuthenticator
{
    /**
     * Authenticate via HTTP Basic credentials.
     *
     * Delegates to Laravel's authentication system.
     */
    public function authenticateBasic(string $username, string $password): bool
    {
        if (!config('ca-est.allow_basic_auth', true)) {
            return false;
        }

        return Auth::once([
            'email' => $username,
            'password' => $password,
        ]) !== false;
    }

    /**
     * Authenticate via client certificate.
     *
     * Validates that the client certificate was issued by the given CA
     * and is currently valid (not expired, not revoked).
     */
    public function authenticateCertificate(string $clientCertPem, CertificateAuthority $ca): bool
    {
        if (!config('ca-est.allow_certificate_auth', true)) {
            return false;
        }

        try {
            $x509 = new X509();
            $certData = $x509->loadX509($clientCertPem);

            if ($certData === false) {
                return false;
            }

            // Extract the serial number from the client certificate
            $serialNumber = $x509->getCurrentCert()['tbsCertificate']['serialNumber']->toString();

            // Look up the certificate in our database
            $certificate = Certificate::query()
                ->forCa($ca->getId())
                ->bySerial($serialNumber)
                ->first();

            if ($certificate === null) {
                return false;
            }

            // Check certificate status
            if ($certificate->status !== CertificateStatus::ACTIVE) {
                return false;
            }

            // Check expiration
            if ($certificate->isExpired()) {
                return false;
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Extract client identity from the request.
     *
     * Checks for HTTP Basic username or client certificate subject.
     */
    public function getClientIdentity(Request $request): ?string
    {
        // Check HTTP Basic auth
        $username = $request->getUser();
        if ($username !== null && $username !== '') {
            return $username;
        }

        // Check client certificate (typically provided via reverse proxy header)
        $clientCert = $request->header('X-SSL-Client-Cert')
            ?? $request->header('X-Client-Cert')
            ?? $request->server('SSL_CLIENT_CERT');

        if ($clientCert !== null && $clientCert !== '') {
            try {
                $x509 = new X509();
                $certData = $x509->loadX509($clientCert);

                if ($certData !== false) {
                    $dn = $x509->getDN(X509::DN_STRING);

                    return is_string($dn) ? $dn : null;
                }
            } catch (\Throwable) {
                // Fall through
            }
        }

        return null;
    }

    /**
     * Get client certificate PEM from the request.
     */
    public function getClientCertPem(Request $request): ?string
    {
        $clientCert = $request->header('X-SSL-Client-Cert')
            ?? $request->header('X-Client-Cert')
            ?? $request->server('SSL_CLIENT_CERT');

        if ($clientCert === null || $clientCert === '') {
            return null;
        }

        // Normalize: if it doesn't look like PEM, try URL-decoded
        if (!str_contains($clientCert, '-----BEGIN')) {
            $decoded = urldecode($clientCert);
            if (str_contains($decoded, '-----BEGIN')) {
                return $decoded;
            }

            return null;
        }

        return $clientCert;
    }

    /**
     * Extract subject DN from client certificate for re-enrollment matching.
     *
     * @return array<string, string>|null
     */
    public function getClientCertSubject(string $clientCertPem): ?array
    {
        try {
            $x509 = new X509();
            $certData = $x509->loadX509($clientCertPem);

            if ($certData === false) {
                return null;
            }

            $dn = $x509->getDN(X509::DN_OPENSSL);

            return is_array($dn) ? $dn : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
