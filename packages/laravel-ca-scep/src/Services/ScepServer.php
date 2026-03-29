<?php

declare(strict_types=1);

namespace CA\Scep\Services;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Models\Certificate;
use CA\Csr\Contracts\CsrManagerInterface;
use CA\DTOs\CertificateOptions;
use CA\Models\CertificateType;
use CA\Models\HashAlgorithm;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Models\CertificateAuthority;
use CA\Scep\Asn1\Maps\ScepPkiMessage;
use CA\Scep\Contracts\ScepServerInterface;
use CA\Models\ScepFailInfo;
use CA\Models\ScepMessageType;
use CA\Models\ScepPkiStatus;
use CA\Scep\Events\ScepCertificateIssued;
use CA\Scep\Events\ScepEnrollmentFailed;
use CA\Scep\Events\ScepEnrollmentRequested;
use CA\Scep\Models\ScepTransaction;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\RSA\PrivateKey as RSAPrivateKey;
use phpseclib3\File\X509;
use RuntimeException;

final class ScepServer implements ScepServerInterface
{
    public function __construct(
        private readonly ScepMessageParser $messageParser,
        private readonly ScepMessageBuilder $messageBuilder,
        private readonly ScepChallengeManager $challengeManager,
        private readonly CertificateManagerInterface $certificateManager,
        private readonly CsrManagerInterface $csrManager,
        private readonly KeyManagerInterface $keyManager,
    ) {}

    /**
     * Handle GetCACert - return the CA certificate(s) as degenerate PKCS#7.
     *
     * If the CA has a chain (intermediate + root), returns all certificates
     * in a PKCS#7 degenerate SignedData structure.
     * If single CA cert, returns the raw DER certificate.
     */
    public function handleGetCACert(CertificateAuthority $ca): string
    {
        $caCert = $this->getCaCertificate($ca);
        $caCertDer = $this->getCertificateDer($caCert);

        // Check if CA has a parent (intermediate CA)
        $chain = $this->certificateManager->getChain($caCert);

        if (count($chain) > 1) {
            // Multiple certs - return as degenerate PKCS#7
            $certsDer = array_map(
                fn(Certificate $cert) => $this->getCertificateDer($cert),
                $chain,
            );

            return $this->messageBuilder->buildDegenerateCertOnly(...$certsDer);
        }

        // Single certificate - return raw DER
        return $caCertDer;
    }

    /**
     * Handle GetCACaps - return capabilities as newline-separated text.
     */
    public function handleGetCACaps(CertificateAuthority $ca): string
    {
        $capabilities = config('ca-scep.capabilities', [
            'AES',
            'POSTPKIOperation',
            'SHA-256',
            'SHA-512',
            'DES3',
            'Renewal',
            'GetNextCACert',
        ]);

        return implode("\n", $capabilities);
    }

    /**
     * Handle PKCSReq - process a PKCS#7 enrollment request.
     *
     * Flow:
     * 1. Parse PKCS#7 SignedData outer envelope
     * 2. Verify signer signature
     * 3. Decrypt EnvelopedData inner content using CA private key
     * 4. Extract PKCS#10 CSR from decrypted content
     * 5. Validate challenge password (if required)
     * 6. Auto-approve or set to pending
     * 7. If approved, sign certificate
     * 8. Build PKCS#7 response (EnvelopedData inside SignedData)
     */
    public function handlePKCSReq(CertificateAuthority $ca, string $pkiMessage): string
    {
        $caCert = $this->getCaCertificate($ca);
        $caCertDer = $this->getCertificateDer($caCert);
        $caKey = $caCert->key;
        $caPrivateKey = $this->keyManager->decryptPrivateKey($caKey);

        if (!$caPrivateKey instanceof RSAPrivateKey) {
            throw new RuntimeException('SCEP requires an RSA CA key.');
        }

        try {
            // Step 1: Parse the outer SignedData
            $scepMessage = $this->messageParser->parse($pkiMessage);

            // Step 2: Decrypt the EnvelopedData
            $scepMessage = $this->messageParser->decryptEnvelopedData($scepMessage, $caPrivateKey);

            // Step 3: Extract CSR
            $csrPem = $this->messageParser->extractCsr($scepMessage);
            $challengePassword = $this->messageParser->extractChallenge($scepMessage);

            // Step 4: Create transaction record
            $recipientNonce = bin2hex(Random::string(16));
            $transaction = ScepTransaction::create([
                'ca_id' => $ca->id,
                'tenant_id' => $ca->tenant_id,
                'transaction_id' => $scepMessage->transactionId,
                'message_type' => $scepMessage->messageType->slug,
                'status' => ScepPkiStatus::PENDING,
                'sender_nonce' => $scepMessage->senderNonce,
                'recipient_nonce' => $recipientNonce,
                'csr_pem' => $csrPem,
                'challenge_password' => $challengePassword,
                'expires_at' => now()->addSeconds((int) config('ca-scep.challenge_password_ttl', 3600)),
            ]);

            // Fire enrollment requested event
            event(new ScepEnrollmentRequested($transaction, $ca));

            // Step 5: Validate challenge password
            if (config('ca-scep.challenge_password_required', true)) {
                if ($challengePassword === null || $challengePassword === '') {
                    return $this->failTransaction(
                        $transaction,
                        ScepFailInfo::fromSlug(ScepFailInfo::BAD_REQUEST),
                        $caCertDer,
                        $caPrivateKey,
                        'Challenge password required but not provided.',
                    );
                }

                if (!$this->challengeManager->validate($ca, $challengePassword)) {
                    return $this->failTransaction(
                        $transaction,
                        ScepFailInfo::fromSlug(ScepFailInfo::BAD_REQUEST),
                        $caCertDer,
                        $caPrivateKey,
                        'Invalid challenge password.',
                    );
                }
            }

            // Step 6: Auto-approve or pend
            if (!config('ca-scep.auto_approve', false)) {
                $transaction->update(['status' => ScepPkiStatus::PENDING]);

                return $this->messageBuilder->buildPending(
                    $transaction,
                    $caCertDer,
                    $caPrivateKey,
                );
            }

            // Step 7: Issue certificate
            return $this->issueCertificateAndRespond(
                $transaction,
                $ca,
                $csrPem,
                $caCertDer,
                $caPrivateKey,
                $scepMessage->signerCertificateDer,
            );
        } catch (RuntimeException $e) {
            // Create a minimal transaction for error response
            $transaction = $transaction ?? ScepTransaction::create([
                'ca_id' => $ca->id,
                'tenant_id' => $ca->tenant_id,
                'transaction_id' => 'error-' . bin2hex(Random::string(8)),
                'message_type' => ScepMessageType::PKCS_REQ,
                'status' => ScepPkiStatus::FAILURE,
                'sender_nonce' => '',
                'recipient_nonce' => bin2hex(Random::string(16)),
                'error_info' => $e->getMessage(),
                'expires_at' => now()->addHour(),
            ]);

            event(new ScepEnrollmentFailed($transaction, $ca, $e->getMessage()));

            return $this->messageBuilder->buildFailure(
                $transaction,
                ScepFailInfo::fromSlug(ScepFailInfo::BAD_MESSAGE_CHECK),
                $caCertDer,
                $caPrivateKey,
            );
        }
    }

    /**
     * Handle GetNextCACert - return next CA certificate for rollover.
     */
    public function handleGetNextCACert(CertificateAuthority $ca): string
    {
        // Return the current CA cert as degenerate PKCS#7
        // In a full implementation, this would return the next CA cert during rollover
        return $this->handleGetCACert($ca);
    }

    /**
     * Handle CertPoll - check enrollment status and return cert if ready.
     */
    public function handleCertPoll(CertificateAuthority $ca, string $pkiMessage): string
    {
        $caCert = $this->getCaCertificate($ca);
        $caCertDer = $this->getCertificateDer($caCert);
        $caKey = $caCert->key;
        $caPrivateKey = $this->keyManager->decryptPrivateKey($caKey);

        if (!$caPrivateKey instanceof RSAPrivateKey) {
            throw new RuntimeException('SCEP requires an RSA CA key.');
        }

        $scepMessage = $this->messageParser->parse($pkiMessage);

        $transaction = ScepTransaction::query()
            ->forCa($ca->id)
            ->byTransactionId($scepMessage->transactionId)
            ->notExpired()
            ->latest()
            ->first();

        if ($transaction === null) {
            $transaction = ScepTransaction::create([
                'ca_id' => $ca->id,
                'tenant_id' => $ca->tenant_id,
                'transaction_id' => $scepMessage->transactionId,
                'message_type' => ScepMessageType::GET_CERT_INITIAL,
                'status' => ScepPkiStatus::FAILURE,
                'sender_nonce' => $scepMessage->senderNonce,
                'recipient_nonce' => bin2hex(Random::string(16)),
                'error_info' => 'Transaction not found.',
                'expires_at' => now()->addHour(),
            ]);

            return $this->messageBuilder->buildFailure(
                $transaction,
                ScepFailInfo::fromSlug(ScepFailInfo::BAD_CERT_ID),
                $caCertDer,
                $caPrivateKey,
            );
        }

        if ($transaction->status === ScepPkiStatus::SUCCESS && $transaction->certificate_id !== null) {
            $issuedCert = $transaction->certificate;
            $issuedCertDer = $this->getCertificateDer($issuedCert);

            return $this->messageBuilder->buildCertRep(
                $transaction,
                $issuedCertDer,
                $caCertDer,
                $caPrivateKey,
                $scepMessage->signerCertificateDer,
            );
        }

        if ($transaction->status === ScepPkiStatus::PENDING) {
            return $this->messageBuilder->buildPending(
                $transaction,
                $caCertDer,
                $caPrivateKey,
            );
        }

        return $this->messageBuilder->buildFailure(
            $transaction,
            ScepFailInfo::fromSlug(ScepFailInfo::BAD_REQUEST),
            $caCertDer,
            $caPrivateKey,
        );
    }

    /**
     * Handle GetCert - retrieve an issued certificate by serial/issuer.
     */
    public function handleGetCert(CertificateAuthority $ca, string $pkiMessage): string
    {
        $caCert = $this->getCaCertificate($ca);
        $caCertDer = $this->getCertificateDer($caCert);
        $caKey = $caCert->key;
        $caPrivateKey = $this->keyManager->decryptPrivateKey($caKey);

        if (!$caPrivateKey instanceof RSAPrivateKey) {
            throw new RuntimeException('SCEP requires an RSA CA key.');
        }

        $scepMessage = $this->messageParser->parse($pkiMessage);

        // Look up by transaction ID
        $transaction = ScepTransaction::query()
            ->forCa($ca->id)
            ->byTransactionId($scepMessage->transactionId)
            ->byStatus(ScepPkiStatus::fromSlug(ScepPkiStatus::SUCCESS))
            ->latest()
            ->first();

        if ($transaction === null || $transaction->certificate_id === null) {
            $errorTransaction = ScepTransaction::create([
                'ca_id' => $ca->id,
                'tenant_id' => $ca->tenant_id,
                'transaction_id' => $scepMessage->transactionId,
                'message_type' => ScepMessageType::GET_CERT,
                'status' => ScepPkiStatus::FAILURE,
                'sender_nonce' => $scepMessage->senderNonce,
                'recipient_nonce' => bin2hex(Random::string(16)),
                'error_info' => 'Certificate not found.',
                'expires_at' => now()->addHour(),
            ]);

            return $this->messageBuilder->buildFailure(
                $errorTransaction,
                ScepFailInfo::fromSlug(ScepFailInfo::BAD_CERT_ID),
                $caCertDer,
                $caPrivateKey,
            );
        }

        $issuedCert = $transaction->certificate;
        $issuedCertDer = $this->getCertificateDer($issuedCert);

        return $this->messageBuilder->buildCertRep(
            $transaction,
            $issuedCertDer,
            $caCertDer,
            $caPrivateKey,
            $scepMessage->signerCertificateDer,
        );
    }

    /**
     * Handle GetCRL - retrieve the certificate revocation list.
     */
    public function handleGetCRL(CertificateAuthority $ca, string $pkiMessage): string
    {
        $caCert = $this->getCaCertificate($ca);
        $caCertDer = $this->getCertificateDer($caCert);
        $caKey = $caCert->key;
        $caPrivateKey = $this->keyManager->decryptPrivateKey($caKey);

        if (!$caPrivateKey instanceof RSAPrivateKey) {
            throw new RuntimeException('SCEP requires an RSA CA key.');
        }

        $scepMessage = $this->messageParser->parse($pkiMessage);

        // Build a failure response as CRL retrieval is a placeholder
        // In production, integrate with your CRL generation service
        $transaction = ScepTransaction::create([
            'ca_id' => $ca->id,
            'tenant_id' => $ca->tenant_id,
            'transaction_id' => $scepMessage->transactionId,
            'message_type' => ScepMessageType::GET_CRL,
            'status' => ScepPkiStatus::FAILURE,
            'sender_nonce' => $scepMessage->senderNonce,
            'recipient_nonce' => bin2hex(Random::string(16)),
            'error_info' => 'CRL retrieval not yet implemented.',
            'expires_at' => now()->addHour(),
        ]);

        return $this->messageBuilder->buildFailure(
            $transaction,
            ScepFailInfo::fromSlug(ScepFailInfo::BAD_REQUEST),
            $caCertDer,
            $caPrivateKey,
        );
    }

    /**
     * Issue a certificate from a CSR and build the SCEP success response.
     */
    private function issueCertificateAndRespond(
        ScepTransaction $transaction,
        CertificateAuthority $ca,
        string $csrPem,
        string $caCertDer,
        RSAPrivateKey $caPrivateKey,
        string $clientCertDer,
    ): string {
        try {
            // Import and approve the CSR
            $csr = $this->csrManager->import($csrPem);
            $csr = $this->csrManager->approve($csr, 'scep-auto-approve');

            // Build certificate options
            $options = new CertificateOptions(
                type: CertificateType::CLIENT_MTLS,
                validityDays: 365,
                hashAlgorithm: HashAlgorithm::SHA256,
            );

            // Issue the certificate
            $certificate = $this->certificateManager->issueFromCsr($ca, $csr, $options);
            $issuedCertDer = $this->getCertificateDer($certificate);

            // Update transaction
            $transaction->update([
                'status' => ScepPkiStatus::SUCCESS,
                'certificate_id' => $certificate->id,
            ]);

            // Fire success event
            event(new ScepCertificateIssued($transaction, $ca, $certificate));

            // Build CertRep SUCCESS response
            return $this->messageBuilder->buildCertRep(
                $transaction,
                $issuedCertDer,
                $caCertDer,
                $caPrivateKey,
                $clientCertDer,
                config('ca-scep.allowed_algorithms.0', 'aes-256-cbc'),
                config('ca-scep.allowed_hash.0', 'sha256'),
            );
        } catch (\Throwable $e) {
            return $this->failTransaction(
                $transaction,
                ScepFailInfo::fromSlug(ScepFailInfo::BAD_REQUEST),
                $caCertDer,
                $caPrivateKey,
                'Certificate issuance failed: ' . $e->getMessage(),
            );
        }
    }

    /**
     * Mark a transaction as failed and build a failure response.
     */
    private function failTransaction(
        ScepTransaction $transaction,
        ScepFailInfo $failInfo,
        string $caCertDer,
        RSAPrivateKey $caPrivateKey,
        string $errorMessage = '',
    ): string {
        $transaction->update([
            'status' => ScepPkiStatus::FAILURE,
            'error_info' => $errorMessage,
        ]);

        $ca = $transaction->ca;
        event(new ScepEnrollmentFailed($transaction, $ca, $errorMessage));

        return $this->messageBuilder->buildFailure(
            $transaction,
            $failInfo,
            $caCertDer,
            $caPrivateKey,
        );
    }

    /**
     * Get the active CA certificate for the given CA.
     */
    private function getCaCertificate(CertificateAuthority $ca): Certificate
    {
        $cert = Certificate::query()
            ->forCa($ca->id)
            ->active()
            ->where(function ($query) {
                $query->where('type', CertificateType::ROOT_CA)
                    ->orWhere('type', CertificateType::INTERMEDIATE_CA);
            })
            ->latest()
            ->first();

        if ($cert === null) {
            throw new RuntimeException('No active CA certificate found for CA: ' . $ca->id);
        }

        return $cert;
    }

    /**
     * Get DER-encoded certificate data.
     */
    private function getCertificateDer(Certificate $certificate): string
    {
        if ($certificate->certificate_der !== null && $certificate->certificate_der !== '') {
            return $certificate->certificate_der;
        }

        // Convert from PEM to DER
        $pem = $certificate->certificate_pem;
        $pem = preg_replace('/-----[A-Z ]+-----/', '', $pem);
        $pem = str_replace(["\r", "\n", ' '], '', $pem);

        return base64_decode($pem, strict: true) ?: throw new RuntimeException(
            'Failed to decode certificate PEM to DER.',
        );
    }
}
