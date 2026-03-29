<?php

declare(strict_types=1);

namespace CA\Acme\Services;

use CA\Acme\Contracts\AcmeServerInterface;
use CA\Acme\Contracts\ChallengeValidatorInterface;
use CA\Models\AuthorizationStatus;
use CA\Models\ChallengeStatus;
use CA\Models\ChallengeType;
use CA\Models\OrderStatus;
use CA\Acme\Events\AcmeAccountCreated;
use CA\Acme\Events\AcmeCertificateIssued;
use CA\Acme\Events\AcmeOrderCreated;
use CA\Acme\Events\AcmeOrderFinalized;
use CA\Acme\Models\AcmeAccount;
use CA\Acme\Models\AcmeAuthorization;
use CA\Acme\Models\AcmeChallenge;
use CA\Acme\Models\AcmeOrder;
use CA\Acme\Services\Challenges\Dns01Validator;
use CA\Acme\Services\Challenges\Http01Validator;
use CA\Acme\Services\Challenges\TlsAlpn01Validator;
use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Csr\Contracts\CsrManagerInterface;
use CA\DTOs\CertificateOptions;
use CA\DTOs\DistinguishedName;
use CA\Models\CertificateType;
use CA\Models\RevocationReason;
use CA\Models\CertificateAuthority;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AcmeServer implements AcmeServerInterface
{
    public function __construct(
        private readonly JwsVerifier $jwsVerifier,
        private readonly NonceManager $nonceManager,
        private readonly CertificateManagerInterface $certificateManager,
        private readonly CsrManagerInterface $csrManager,
    ) {}

    /**
     * Return the ACME directory object with endpoint URLs.
     *
     * @return array<string, mixed>
     */
    public function directory(): array
    {
        $prefix = rtrim(config('ca-acme.route_prefix', 'acme'), '/');

        return [
            'newNonce' => url("{$prefix}/new-nonce"),
            'newAccount' => url("{$prefix}/new-account"),
            'newOrder' => url("{$prefix}/new-order"),
            'revokeCert' => url("{$prefix}/revoke-cert"),
            'keyChange' => url("{$prefix}/key-change"),
            'meta' => array_filter([
                'termsOfService' => config('ca-acme.terms_of_service_url'),
                'website' => config('ca-acme.website_url'),
                'externalAccountRequired' => config('ca-acme.external_account_required', false),
            ], static fn(mixed $v): bool => $v !== null && $v !== false),
        ];
    }

    /**
     * Generate and store a new nonce.
     */
    public function newNonce(): string
    {
        return $this->nonceManager->generate();
    }

    /**
     * Create or retrieve an ACME account.
     *
     * @param  array<string, mixed>  $payload
     */
    public function newAccount(array $payload, string $jwk): AcmeAccount
    {
        $jwkArray = json_decode($jwk, true);

        if (!is_array($jwkArray)) {
            throw new RuntimeException('Invalid JWK format.');
        }

        $thumbprint = $this->jwsVerifier->computeThumbprint($jwkArray);

        // Check for existing account
        $existing = AcmeAccount::where('account_key_thumbprint', $thumbprint)->first();

        $onlyReturnExisting = $payload['onlyReturnExisting'] ?? false;

        if ($existing !== null) {
            return $existing;
        }

        if ($onlyReturnExisting) {
            throw new RuntimeException('Account does not exist.');
        }

        // Verify ToS agreement if required
        $tosUrl = config('ca-acme.terms_of_service_url');

        if ($tosUrl !== null && !($payload['termsOfServiceAgreed'] ?? false)) {
            throw new RuntimeException('Terms of service must be agreed to.');
        }

        $caId = config('ca-acme.ca_id');

        if ($caId === null) {
            throw new RuntimeException('No default CA configured for ACME.');
        }

        $account = AcmeAccount::create([
            'ca_id' => $caId,
            'status' => 'valid',
            'contact' => $payload['contact'] ?? [],
            'public_key_jwk' => $jwkArray,
            'account_key_thumbprint' => $thumbprint,
            'terms_agreed' => $payload['termsOfServiceAgreed'] ?? false,
            'external_account_id' => $payload['externalAccountBinding']['kid'] ?? null,
        ]);

        event(new AcmeAccountCreated($account));

        return $account;
    }

    /**
     * Create a new order for certificate issuance.
     *
     * @param  array<array{type: string, value: string}>  $identifiers
     */
    public function newOrder(AcmeAccount $account, array $identifiers): AcmeOrder
    {
        $validityHours = (int) config('ca-acme.order_validity_hours', 168);
        $authValidityHours = (int) config('ca-acme.authorization_validity_hours', 168);
        $enabledTypes = config('ca-acme.challenge_types', ['http-01', 'dns-01', 'tls-alpn-01']);

        $order = DB::transaction(function () use ($account, $identifiers, $validityHours, $authValidityHours, $enabledTypes): AcmeOrder {
            $order = AcmeOrder::create([
                'account_id' => $account->id,
                'ca_id' => $account->ca_id,
                'tenant_id' => $account->tenant_id,
                'status' => OrderStatus::PENDING,
                'identifiers' => $identifiers,
                'expires_at' => now()->addHours($validityHours),
            ]);

            foreach ($identifiers as $identifier) {
                $isWildcard = str_starts_with($identifier['value'], '*.');
                $domain = $isWildcard ? substr($identifier['value'], 2) : $identifier['value'];

                $authorization = AcmeAuthorization::create([
                    'order_id' => $order->id,
                    'identifier_type' => $identifier['type'],
                    'identifier_value' => $domain,
                    'status' => AuthorizationStatus::PENDING,
                    'wildcard' => $isWildcard,
                    'expires_at' => now()->addHours($authValidityHours),
                ]);

                foreach ($enabledTypes as $challengeTypeValue) {
                    // Wildcard domains only support dns-01
                    if ($isWildcard && $challengeTypeValue !== 'dns-01') {
                        continue;
                    }

                    $challengeType = $challengeTypeValue;

                    AcmeChallenge::create([
                        'authorization_id' => $authorization->id,
                        'type' => $challengeType,
                        'status' => ChallengeStatus::PENDING,
                        'token' => JwsVerifier::base64UrlEncode(random_bytes(32)),
                    ]);
                }
            }

            return $order;
        });

        $order->load('authorizations.challenges');

        event(new AcmeOrderCreated($order));

        return $order;
    }

    /**
     * Retrieve an order by its UUID.
     */
    public function getOrder(string $url): AcmeOrder
    {
        // Extract UUID from URL or use directly
        $uuid = basename($url);

        $order = AcmeOrder::where('id', $uuid)->first();

        if ($order === null) {
            throw new RuntimeException('Order not found.');
        }

        $order->load('authorizations.challenges');

        return $order;
    }

    /**
     * Finalize an order by submitting a CSR.
     */
    public function finalizeOrder(AcmeOrder $order, string $csr): AcmeOrder
    {
        if ($order->status !== OrderStatus::READY) {
            throw new RuntimeException("Order is not ready for finalization. Current status: {$order->status}");
        }

        // Decode the base64url-encoded CSR
        $csrDer = JwsVerifier::base64UrlDecode($csr);
        $csrPem = "-----BEGIN CERTIFICATE REQUEST-----\n"
            . chunk_split(base64_encode($csrDer), 64)
            . "-----END CERTIFICATE REQUEST-----";

        $order->update([
            'status' => OrderStatus::PROCESSING,
            'finalize_csr' => $csrPem,
        ]);

        try {
            // Import and validate the CSR
            $csrModel = $this->csrManager->import($csrPem);

            // Extract identifiers from CSR and verify they match the order
            $csrDn = $this->csrManager->getSubjectDN($csrModel);
            $this->validateCsrIdentifiers($order, $csrDn);

            // Get the CA
            $ca = CertificateAuthority::findOrFail($order->ca_id);

            // Build certificate options
            $options = new CertificateOptions(
                validityDays: 90,
                type: CertificateType::SERVER_TLS,
            );

            // Issue the certificate
            $certificate = $this->certificateManager->issueFromCsr($ca, $csrModel, $options);

            $order->update([
                'status' => OrderStatus::VALID,
                'certificate_id' => $certificate->id,
            ]);

            event(new AcmeOrderFinalized($order));
            event(new AcmeCertificateIssued($order, $certificate));
        } catch (\Throwable $e) {
            $order->update([
                'status' => OrderStatus::INVALID,
            ]);

            throw new RuntimeException('Failed to finalize order: ' . $e->getMessage(), 0, $e);
        }

        return $order->fresh();
    }

    /**
     * Retrieve the issued certificate PEM chain.
     */
    public function getCertificate(AcmeOrder $order): string
    {
        if ($order->status !== OrderStatus::VALID || $order->certificate_id === null) {
            throw new RuntimeException('Certificate is not available for this order.');
        }

        $certificate = $order->certificate;

        if ($certificate === null) {
            throw new RuntimeException('Certificate not found.');
        }

        // Get the full chain
        $chain = $this->certificateManager->getChain($certificate);
        $pemChain = '';

        foreach ($chain as $cert) {
            $pemChain .= $cert->certificate_pem . "\n";
        }

        return trim($pemChain);
    }

    /**
     * Revoke a certificate.
     */
    public function revokeCertificate(string $certPem, ?int $reason = null): void
    {
        $revocationReason = $reason !== null
            ? RevocationReason::tryFrom($reason) ?? RevocationReason::UNSPECIFIED
            : RevocationReason::UNSPECIFIED;

        // Find the certificate by DER fingerprint (colon-separated hex, matching KeyManager pattern)
        $derBytes = $this->pemToDer($certPem);
        $hash = hash('sha256', $derBytes);
        $fingerprint = implode(':', str_split($hash, 2));
        $certificate = \CA\Crt\Models\Certificate::where('fingerprint_sha256', $fingerprint)->first();

        if ($certificate === null) {
            // Try matching by PEM content
            $certificate = \CA\Crt\Models\Certificate::where('certificate_pem', $certPem)->first();
        }

        if ($certificate === null) {
            throw new RuntimeException('Certificate not found.');
        }

        $this->certificateManager->revoke($certificate, $revocationReason);

        // Update any associated orders
        AcmeOrder::where('certificate_id', $certificate->id)
            ->update(['status' => OrderStatus::REVOKED]);
    }

    /**
     * Get the challenge validator for a given type.
     */
    public function getChallengeValidator(string $type): ChallengeValidatorInterface
    {
        return match ($type) {
            ChallengeType::HTTP_01 => new Http01Validator(),
            ChallengeType::DNS_01 => new Dns01Validator(),
            ChallengeType::TLS_ALPN_01 => new TlsAlpn01Validator(),
            default => throw new RuntimeException("Unsupported challenge type: {$type}"),
        };
    }

    /**
     * Validate that CSR identifiers match the order identifiers.
     */
    /**
     * Convert PEM-encoded data to DER bytes.
     */
    private function pemToDer(string $pem): string
    {
        $pem = trim($pem);
        $base64 = preg_replace('/-----[A-Z0-9 ]+-----/', '', $pem);
        $base64 = preg_replace('/\s+/', '', $base64);

        $der = base64_decode($base64, true);
        if ($der === false) {
            throw new RuntimeException('Invalid PEM encoding.');
        }

        return $der;
    }

    private function validateCsrIdentifiers(AcmeOrder $order, DistinguishedName $csrDn): void
    {
        $orderDomains = collect($order->identifiers)
            ->where('type', 'dns')
            ->pluck('value')
            ->sort()
            ->values()
            ->toArray();

        // The CN from the CSR should match one of the order identifiers
        if ($csrDn->commonName !== null && !in_array($csrDn->commonName, $orderDomains, true)) {
            throw new RuntimeException(
                'CSR common name does not match any order identifier.',
            );
        }
    }
}
