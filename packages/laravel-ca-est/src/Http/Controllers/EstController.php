<?php

declare(strict_types=1);

namespace CA\Est\Http\Controllers;

use CA\Est\Contracts\EstServerInterface;
use CA\Est\Services\EstAuthenticator;
use CA\Models\CertificateAuthority;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * EST Protocol Controller per RFC 7030.
 *
 * All endpoints are served under /.well-known/est/{label}/ where {label}
 * identifies the Certificate Authority.
 */
class EstController extends Controller
{
    public function __construct(
        private readonly EstServerInterface $estServer,
        private readonly EstAuthenticator $authenticator,
    ) {}

    /**
     * GET /cacerts - CA Certificates Request (RFC 7030 Section 4.1).
     *
     * No authentication required. Returns the CA certificate chain
     * as a PKCS#7 certs-only response.
     */
    public function getCaCerts(Request $request, string $label): Response
    {
        $ca = $this->resolveCA($label);

        $body = $this->estServer->getCaCerts($ca);

        return $this->buildPkcs7Response($body);
    }

    /**
     * POST /simpleenroll - Simple Enrollment (RFC 7030 Section 4.2).
     *
     * Requires authentication. Accepts a PKCS#10 CSR and returns
     * the issued certificate as PKCS#7.
     */
    public function simpleEnroll(Request $request, string $label): Response
    {
        $ca = $this->resolveCA($label);

        $csrBase64 = $request->getContent();

        $auth = [
            'identity' => $this->authenticator->getClientIdentity($request),
        ];

        $body = $this->estServer->simpleEnroll($ca, $csrBase64, $auth);

        return $this->buildPkcs7Response($body, 200);
    }

    /**
     * POST /simplereenroll - Simple Re-enrollment (RFC 7030 Section 4.2.2).
     *
     * Requires client certificate authentication. Accepts a PKCS#10 CSR
     * and returns the re-issued certificate as PKCS#7.
     */
    public function simpleReenroll(Request $request, string $label): Response
    {
        $ca = $this->resolveCA($label);

        $csrBase64 = $request->getContent();

        $clientCertPem = $this->authenticator->getClientCertPem($request);

        $clientCert = [
            'identity' => $this->authenticator->getClientIdentity($request),
            'pem' => $clientCertPem,
            'subject' => $clientCertPem !== null
                ? $this->authenticator->getClientCertSubject($clientCertPem)
                : null,
        ];

        $body = $this->estServer->simpleReenroll($ca, $csrBase64, $clientCert);

        return $this->buildPkcs7Response($body, 200);
    }

    /**
     * POST /serverkeygen - Server-Side Key Generation (RFC 7030 Section 4.4).
     *
     * Requires authentication. Generates a key pair, issues a certificate,
     * and returns both as a multipart response.
     */
    public function serverKeyGen(Request $request, string $label): Response
    {
        $ca = $this->resolveCA($label);

        $csrBase64 = $request->getContent();

        $auth = [
            'identity' => $this->authenticator->getClientIdentity($request),
        ];

        $result = $this->estServer->serverKeyGen($ca, $csrBase64, $auth);

        return $this->buildServerKeyGenResponse($result);
    }

    /**
     * POST /fullcmc - Full CMC (RFC 7030 Section 4.3).
     *
     * Requires authentication. Processes a CMC request.
     */
    public function fullCmc(Request $request, string $label): Response
    {
        $ca = $this->resolveCA($label);

        $cmcRequest = $request->getContent();

        $body = $this->estServer->fullCmc($ca, $cmcRequest);

        return new Response($body, 200, [
            'Content-Type' => 'application/pkcs7-mime; smime-type=certs-only',
            'Content-Transfer-Encoding' => 'base64',
        ]);
    }

    /**
     * GET /csrattrs - CSR Attributes (RFC 7030 Section 4.5.2).
     *
     * No authentication required. Returns suggested CSR attributes.
     */
    public function getCsrAttrs(Request $request, string $label): Response
    {
        $ca = $this->resolveCA($label);

        $body = $this->estServer->getCsrAttrs($ca);

        return new Response($body, 200, [
            'Content-Type' => 'application/csrattrs',
            'Content-Transfer-Encoding' => 'base64',
        ]);
    }

    /**
     * Resolve a CertificateAuthority by label (UUID or slug).
     */
    private function resolveCA(string $label): CertificateAuthority
    {
        // Try by UUID first
        $ca = CertificateAuthority::find($label);

        if ($ca === null) {
            // Try by metadata label/slug
            $ca = CertificateAuthority::query()
                ->where('metadata->est_label', $label)
                ->active()
                ->first();
        }

        if ($ca === null) {
            // Fall back to default CA from config
            $defaultCaId = config('ca-est.ca_id');
            if ($defaultCaId !== null) {
                $ca = CertificateAuthority::find($defaultCaId);
            }
        }

        if ($ca === null) {
            abort(404, 'Certificate Authority not found.');
        }

        return $ca;
    }

    /**
     * Build a standard PKCS#7 certs-only response.
     */
    private function buildPkcs7Response(string $body, int $status = 200): Response
    {
        return new Response($body, $status, [
            'Content-Type' => 'application/pkcs7-mime; smime-type=certs-only',
            'Content-Transfer-Encoding' => 'base64',
        ]);
    }

    /**
     * Build a multipart response for server-side key generation.
     *
     * @param  array{private_key: string, certificate: string, content_type: string}  $result
     */
    private function buildServerKeyGenResponse(array $result): Response
    {
        $boundary = 'est-server-keygen-' . bin2hex(random_bytes(16));

        $body = "--{$boundary}\r\n";
        $body .= "Content-Type: application/pkcs8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "\r\n";
        $body .= $result['private_key'] . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: application/pkcs7-mime; smime-type=certs-only\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "\r\n";
        $body .= $result['certificate'] . "\r\n";
        $body .= "--{$boundary}--\r\n";

        return new Response($body, 200, [
            'Content-Type' => "multipart/mixed; boundary={$boundary}",
        ]);
    }
}
