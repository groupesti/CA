<?php

declare(strict_types=1);

namespace CA\Est\Contracts;

use CA\Models\CertificateAuthority;

interface EstServerInterface
{
    /**
     * Get CA certificates (PKCS#7 certs-only).
     *
     * Returns base64-encoded degenerate PKCS#7 SignedData containing
     * the CA certificate chain per RFC 7030 Section 4.1.
     */
    public function getCaCerts(CertificateAuthority $ca): string;

    /**
     * Simple enrollment (RFC 7030 Section 4.2).
     *
     * Accepts a base64-encoded PKCS#10 CSR, validates and issues a certificate.
     * Returns base64-encoded PKCS#7 containing the new certificate.
     *
     * @param  array<string, mixed>|null  $auth  Authentication context
     */
    public function simpleEnroll(CertificateAuthority $ca, string $csrBase64, ?array $auth = null): string;

    /**
     * Simple re-enrollment (RFC 7030 Section 4.2.2).
     *
     * Same as simpleEnroll but validates that the client already holds
     * a valid certificate from this CA.
     *
     * @param  array<string, mixed>  $clientCert  Client certificate information
     */
    public function simpleReenroll(CertificateAuthority $ca, string $csrBase64, array $clientCert): string;

    /**
     * Server-side key generation (RFC 7030 Section 4.4).
     *
     * Generates a new key pair on the server, creates a CSR internally,
     * issues a certificate, and returns both the private key and certificate.
     *
     * @param  array<string, mixed>|null  $auth  Authentication context
     * @return array{private_key: string, certificate: string}
     */
    public function serverKeyGen(CertificateAuthority $ca, string $csrBase64, ?array $auth = null): array;

    /**
     * Full CMC request (RFC 7030 Section 4.3).
     *
     * Processes a CMC (Certificate Management over CMS) request and returns
     * a CMC response.
     */
    public function fullCmc(CertificateAuthority $ca, string $cmcRequest): string;

    /**
     * Get CSR attributes (RFC 7030 Section 4.5.2).
     *
     * Returns base64-encoded ASN.1 SEQUENCE OF AttrOrOID describing
     * the suggested CSR attributes for this CA.
     */
    public function getCsrAttrs(CertificateAuthority $ca): string;
}
