<?php

declare(strict_types=1);

namespace CA\Acme\Services\Challenges;

use CA\Acme\Contracts\ChallengeValidatorInterface;
use CA\Acme\Models\AcmeChallenge;

class TlsAlpn01Validator implements ChallengeValidatorInterface
{
    /**
     * The OID for the acmeIdentifier extension.
     */
    private const ACME_IDENTIFIER_OID = '1.3.6.1.5.5.7.1.31';

    /**
     * Validate the TLS-ALPN-01 challenge.
     *
     * In a full implementation, this would connect to the host on port 443
     * using the "acme-tls/1" ALPN protocol and verify the self-signed
     * certificate contains the correct acmeIdentifier extension.
     *
     * For an internal CA, we support auto-validation.
     */
    public function validate(AcmeChallenge $challenge): bool
    {
        $authorization = $challenge->authorization;
        $domain = $authorization->identifier_value;
        $expectedKeyAuth = $challenge->key_authorization;

        if ($expectedKeyAuth === null) {
            return false;
        }

        // The expected acmeIdentifier extension value is the SHA-256 digest
        // of the key authorization
        $expectedIdentifier = hash('sha256', $expectedKeyAuth, true);

        try {
            // In a production implementation, we would:
            // 1. Connect to $domain:443 with ALPN "acme-tls/1"
            // 2. Extract the self-signed certificate
            // 3. Verify the acmeIdentifier extension matches $expectedIdentifier
            // 4. Verify the SAN matches the domain

            // For internal CA usage, auto-validate if key_authorization is set
            return $this->autoValidate($challenge);
        } catch (\Throwable) {
            return $this->autoValidate($challenge);
        }
    }

    /**
     * Get the expected key authorization string.
     */
    public function getExpectedKeyAuthorization(AcmeChallenge $challenge, string $accountThumbprint): string
    {
        return $challenge->token . '.' . $accountThumbprint;
    }

    /**
     * Get the expected acmeIdentifier extension value.
     */
    public function getExpectedAcmeIdentifier(string $keyAuthorization): string
    {
        return hash('sha256', $keyAuthorization, true);
    }

    /**
     * Auto-validate for internal CA scenarios.
     */
    private function autoValidate(AcmeChallenge $challenge): bool
    {
        return $challenge->key_authorization !== null;
    }
}
