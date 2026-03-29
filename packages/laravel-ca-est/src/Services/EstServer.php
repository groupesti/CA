<?php

declare(strict_types=1);

namespace CA\Est\Services;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Models\Certificate;
use CA\Csr\Contracts\CsrManagerInterface;
use CA\DTOs\CertificateOptions;
use CA\Models\CertificateStatus;
use CA\Models\CertificateType;
use CA\Models\KeyAlgorithm;
use CA\Est\Contracts\EstServerInterface;
use CA\Est\Events\EstCertificateIssued;
use CA\Est\Events\EstEnrollmentRequested;
use CA\Est\Events\EstReenrollmentCompleted;
use CA\Est\Events\EstServerKeyGenerated;
use CA\Est\Models\EstEnrollment;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Models\CertificateAuthority;
use Illuminate\Support\Facades\Log;
use phpseclib3\File\X509;

/**
 * EST Server implementation per RFC 7030.
 *
 * Provides all EST operations: CA certificate distribution, enrollment,
 * re-enrollment, server-side key generation, and CSR attributes.
 */
class EstServer implements EstServerInterface
{
    public function __construct(
        private readonly CertificateManagerInterface $certificateManager,
        private readonly CsrManagerInterface $csrManager,
        private readonly KeyManagerInterface $keyManager,
        private readonly EstResponseBuilder $responseBuilder,
        private readonly EstAuthenticator $authenticator,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getCaCerts(CertificateAuthority $ca): string
    {
        $certPems = $this->collectCaCertChain($ca);

        return $this->responseBuilder->buildCertsOnlyPkcs7($certPems);
    }

    /**
     * {@inheritdoc}
     */
    public function simpleEnroll(CertificateAuthority $ca, string $csrBase64, ?array $auth = null): string
    {
        $csrPem = $this->decodeCsrFromBase64($csrBase64);

        // Create enrollment record
        $enrollment = EstEnrollment::create([
            'ca_id' => $ca->getId(),
            'tenant_id' => $ca->getTenantId(),
            'type' => 'enroll',
            'status' => 'pending',
            'client_identity' => $auth['identity'] ?? null,
            'csr_pem' => $csrPem,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        EstEnrollmentRequested::dispatch($ca, $enrollment, 'enroll', $auth['identity'] ?? null);

        try {
            // Import and validate the CSR
            $csr = $this->csrManager->import($csrPem);

            if (!$this->csrManager->validate($csr)) {
                $enrollment->markFailed();
                throw new \RuntimeException('CSR validation failed.');
            }

            // Approve the CSR automatically for EST
            $csr = $this->csrManager->approve($csr, 'est-auto-enroll');

            // Issue certificate
            $options = CertificateOptions::fromArray([
                'type' => CertificateType::END_ENTITY,
                'validity_days' => (int) config('ca-est.default_validity_days', 365),
            ]);

            $certificate = $this->certificateManager->issueFromCsr($ca, $csr, $options);

            $enrollment->markCompleted($certificate->id);

            EstCertificateIssued::dispatch($ca, $enrollment, $certificate);

            // Return certificate as PKCS#7
            return $this->responseBuilder->buildCertsOnlyPkcs7(
                $this->buildCertResponseChain($certificate, $ca),
            );
        } catch (\Throwable $e) {
            $enrollment->markFailed();
            Log::error('EST simple enrollment failed', [
                'ca_id' => $ca->getId(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function simpleReenroll(CertificateAuthority $ca, string $csrBase64, array $clientCert): string
    {
        $csrPem = $this->decodeCsrFromBase64($csrBase64);

        // Create enrollment record
        $enrollment = EstEnrollment::create([
            'ca_id' => $ca->getId(),
            'tenant_id' => $ca->getTenantId(),
            'type' => 'reenroll',
            'status' => 'pending',
            'client_identity' => $clientCert['identity'] ?? null,
            'csr_pem' => $csrPem,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        EstEnrollmentRequested::dispatch($ca, $enrollment, 'reenroll', $clientCert['identity'] ?? null);

        try {
            // Validate the client certificate is from this CA
            $clientCertPem = $clientCert['pem'] ?? null;
            if ($clientCertPem === null) {
                $enrollment->markFailed();
                throw new \RuntimeException('Client certificate required for re-enrollment.');
            }

            if (!$this->authenticator->authenticateCertificate($clientCertPem, $ca)) {
                $enrollment->markFailed();
                throw new \RuntimeException('Client certificate authentication failed for re-enrollment.');
            }

            // Verify the CSR subject matches the existing certificate subject
            $this->validateReenrollmentSubject($csrPem, $clientCertPem);

            // Import and validate the CSR
            $csr = $this->csrManager->import($csrPem);

            if (!$this->csrManager->validate($csr)) {
                $enrollment->markFailed();
                throw new \RuntimeException('CSR validation failed.');
            }

            $csr = $this->csrManager->approve($csr, 'est-auto-reenroll');

            // Issue new certificate
            $options = CertificateOptions::fromArray([
                'type' => CertificateType::END_ENTITY,
                'validity_days' => (int) config('ca-est.default_validity_days', 365),
            ]);

            $certificate = $this->certificateManager->issueFromCsr($ca, $csr, $options);

            $enrollment->markCompleted($certificate->id);

            EstReenrollmentCompleted::dispatch($ca, $enrollment, $certificate);

            return $this->responseBuilder->buildCertsOnlyPkcs7(
                $this->buildCertResponseChain($certificate, $ca),
            );
        } catch (\Throwable $e) {
            $enrollment->markFailed();
            Log::error('EST re-enrollment failed', [
                'ca_id' => $ca->getId(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serverKeyGen(CertificateAuthority $ca, string $csrBase64, ?array $auth = null): array
    {
        if (!config('ca-est.server_keygen_enabled', true)) {
            throw new \RuntimeException('Server-side key generation is disabled.');
        }

        $csrPem = $this->decodeCsrFromBase64($csrBase64);

        // Create enrollment record
        $enrollment = EstEnrollment::create([
            'ca_id' => $ca->getId(),
            'tenant_id' => $ca->getTenantId(),
            'type' => 'serverkeygen',
            'status' => 'pending',
            'client_identity' => $auth['identity'] ?? null,
            'csr_pem' => $csrPem,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);

        EstEnrollmentRequested::dispatch($ca, $enrollment, 'serverkeygen', $auth['identity'] ?? null);

        try {
            // Determine key algorithm from config
            $algorithmStr = config('ca-est.default_key_algorithm', 'rsa-2048');
            $algorithm = KeyAlgorithm::from($algorithmStr);

            // Generate a new key pair
            $key = $this->keyManager->generate($algorithm, [], $ca->getTenantId());

            // Import the CSR to extract the subject DN
            $csr = $this->csrManager->import($csrPem);
            $subjectDn = $this->csrManager->getSubjectDN($csr);

            // Create a new CSR with the server-generated key
            $serverCsr = $this->csrManager->create($subjectDn, $key);

            $serverCsr = $this->csrManager->approve($serverCsr, 'est-server-keygen');

            // Issue certificate
            $options = CertificateOptions::fromArray([
                'type' => CertificateType::END_ENTITY,
                'validity_days' => (int) config('ca-est.default_validity_days', 365),
            ]);

            $certificate = $this->certificateManager->issueFromCsr($ca, $serverCsr, $options);

            $enrollment->markCompleted($certificate->id, $key->id);

            EstServerKeyGenerated::dispatch($ca, $enrollment, $key, $certificate);

            // Export private key as PEM (PKCS#8)
            $privateKeyPem = $this->keyManager->export(
                $key,
                \CA\Enums\ExportFormat::PEM,
            );

            // Build PKCS#7 for the certificate
            $certPkcs7 = $this->responseBuilder->buildCertsOnlyPkcs7(
                $this->buildCertResponseChain($certificate, $ca),
            );

            return $this->responseBuilder->buildServerKeyGenResponse($privateKeyPem, $certPkcs7);
        } catch (\Throwable $e) {
            $enrollment->markFailed();
            Log::error('EST server key generation failed', [
                'ca_id' => $ca->getId(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fullCmc(CertificateAuthority $ca, string $cmcRequest): string
    {
        if (!config('ca-est.cmc_enabled', false)) {
            throw new \RuntimeException('Full CMC is not enabled.');
        }

        // Basic CMC implementation: extract PKCS#10 from the CMC request
        // and process as a simple enrollment.
        //
        // A full CMC implementation would parse the CMS SignedData,
        // extract PKIData, process control attributes, etc.
        // This is a simplified version that handles the basic case.

        try {
            $cmcDer = base64_decode($cmcRequest, true);
            if ($cmcDer === false) {
                throw new \RuntimeException('Invalid CMC request encoding.');
            }

            // For the basic case, attempt to extract a PKCS#10 CSR
            // from the CMC PKIData structure.
            // In a full implementation, this would parse the CMS structure.
            Log::info('EST Full CMC request received', [
                'ca_id' => $ca->getId(),
                'size' => strlen($cmcDer),
            ]);

            // Attempt simple processing: treat as containing a CSR
            $csrBase64 = base64_encode($cmcDer);

            return $this->simpleEnroll($ca, $csrBase64);
        } catch (\Throwable $e) {
            Log::error('EST Full CMC processing failed', [
                'ca_id' => $ca->getId(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCsrAttrs(CertificateAuthority $ca): string
    {
        // Suggested CSR attributes per RFC 7030 Section 4.5.2
        $oids = [
            '1.2.840.113549.1.9.7',    // challengePassword
            '1.2.840.113549.1.9.14',   // extensionRequest
            '1.2.840.113549.1.1.11',   // sha256WithRSAEncryption
            '1.2.840.10045.4.3.2',     // ecdsa-with-SHA256
        ];

        return $this->responseBuilder->buildCsrAttrs($oids);
    }

    /**
     * Collect the CA certificate chain as PEM strings.
     *
     * @return array<int, string>
     */
    private function collectCaCertChain(CertificateAuthority $ca): array
    {
        $certPems = [];

        // Get the CA's own certificate
        $caCert = Certificate::query()
            ->forCa($ca->getId())
            ->whereIn('type', [CertificateType::ROOT_CA, CertificateType::INTERMEDIATE_CA])
            ->active()
            ->orderBy('created_at', 'desc')
            ->first();

        if ($caCert !== null && $caCert->certificate_pem) {
            $certPems[] = $caCert->certificate_pem;

            // Walk up the chain to root
            $current = $caCert;
            while ($current->issuer_certificate_id !== null) {
                $issuer = $current->issuerCertificate;
                if ($issuer === null || $issuer->certificate_pem === null) {
                    break;
                }
                $certPems[] = $issuer->certificate_pem;
                $current = $issuer;
            }
        }

        return $certPems;
    }

    /**
     * Build certificate chain for an enrollment response.
     *
     * @return array<int, string>
     */
    private function buildCertResponseChain(Certificate $certificate, CertificateAuthority $ca): array
    {
        $certPems = [];

        if ($certificate->certificate_pem) {
            $certPems[] = $certificate->certificate_pem;
        }

        // Include CA chain
        $caCerts = $this->collectCaCertChain($ca);
        foreach ($caCerts as $caPem) {
            $certPems[] = $caPem;
        }

        return $certPems;
    }

    /**
     * Decode a base64-encoded PKCS#10 CSR to PEM format.
     */
    private function decodeCsrFromBase64(string $csrBase64): string
    {
        // Remove whitespace
        $csrBase64 = preg_replace('/\s+/', '', $csrBase64);

        $csrDer = base64_decode($csrBase64, true);
        if ($csrDer === false) {
            throw new \RuntimeException('Invalid base64-encoded CSR.');
        }

        // Convert DER to PEM
        $base64 = chunk_split(base64_encode($csrDer), 64, "\n");

        return "-----BEGIN CERTIFICATE REQUEST-----\n" . $base64 . "-----END CERTIFICATE REQUEST-----";
    }

    /**
     * Validate that the CSR subject matches the client certificate subject for re-enrollment.
     */
    private function validateReenrollmentSubject(string $csrPem, string $clientCertPem): void
    {
        try {
            // Parse CSR subject
            $csrX509 = new X509();
            $csrData = $csrX509->loadCSR($csrPem);
            if ($csrData === false) {
                throw new \RuntimeException('Failed to parse CSR for subject validation.');
            }
            $csrSubject = $csrX509->getDN(X509::DN_STRING);

            // Parse client cert subject
            $certX509 = new X509();
            $certData = $certX509->loadX509($clientCertPem);
            if ($certData === false) {
                throw new \RuntimeException('Failed to parse client certificate for subject validation.');
            }
            $certSubject = $certX509->getDN(X509::DN_STRING);

            // Subjects must match for re-enrollment
            if ($csrSubject !== $certSubject) {
                throw new \RuntimeException(
                    'CSR subject does not match client certificate subject for re-enrollment.',
                );
            }
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Subject validation failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
