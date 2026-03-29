<?php

declare(strict_types=1);

namespace CA\Acme\Contracts;

use CA\Acme\Models\AcmeAccount;
use CA\Acme\Models\AcmeOrder;

interface AcmeServerInterface
{
    /**
     * Return the ACME directory object with endpoint URLs.
     *
     * @return array<string, mixed>
     */
    public function directory(): array;

    /**
     * Generate and store a new nonce.
     */
    public function newNonce(): string;

    /**
     * Create or retrieve an ACME account.
     *
     * @param  array<string, mixed>  $payload
     */
    public function newAccount(array $payload, string $jwk): AcmeAccount;

    /**
     * Create a new order for certificate issuance.
     *
     * @param  array<array{type: string, value: string}>  $identifiers
     */
    public function newOrder(AcmeAccount $account, array $identifiers): AcmeOrder;

    /**
     * Retrieve an order by its URL/UUID.
     */
    public function getOrder(string $url): AcmeOrder;

    /**
     * Finalize an order by submitting a CSR.
     */
    public function finalizeOrder(AcmeOrder $order, string $csr): AcmeOrder;

    /**
     * Retrieve the issued certificate PEM chain.
     */
    public function getCertificate(AcmeOrder $order): string;

    /**
     * Revoke a certificate.
     */
    public function revokeCertificate(string $certPem, ?int $reason = null): void;
}
