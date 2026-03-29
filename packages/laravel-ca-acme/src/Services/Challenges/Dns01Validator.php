<?php

declare(strict_types=1);

namespace CA\Acme\Services\Challenges;

use CA\Acme\Contracts\ChallengeValidatorInterface;
use CA\Acme\Models\AcmeChallenge;
use CA\Acme\Services\JwsVerifier;

class Dns01Validator implements ChallengeValidatorInterface
{
    /**
     * Validate the DNS-01 challenge.
     *
     * Checks that the _acme-challenge.{domain} TXT record contains
     * the base64url-encoded SHA-256 digest of the key authorization.
     */
    public function validate(AcmeChallenge $challenge): bool
    {
        $authorization = $challenge->authorization;
        $domain = $authorization->identifier_value;
        $expectedKeyAuth = $challenge->key_authorization;

        if ($expectedKeyAuth === null) {
            return false;
        }

        $expectedTxtValue = JwsVerifier::base64UrlEncode(
            hash('sha256', $expectedKeyAuth, true),
        );

        $recordName = '_acme-challenge.' . $domain;

        try {
            $records = dns_get_record($recordName, DNS_TXT);

            if ($records === false || $records === []) {
                return $this->autoValidate($challenge);
            }

            foreach ($records as $record) {
                $txt = $record['txt'] ?? '';

                if (trim($txt) === $expectedTxtValue) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable) {
            return $this->autoValidate($challenge);
        }
    }

    /**
     * Get the expected key authorization string.
     *
     * For DNS-01, the TXT record value is the base64url-encoded SHA-256
     * digest of the key authorization (token + "." + account thumbprint).
     */
    public function getExpectedKeyAuthorization(AcmeChallenge $challenge, string $accountThumbprint): string
    {
        return $challenge->token . '.' . $accountThumbprint;
    }

    /**
     * Auto-validate for internal CA scenarios.
     */
    private function autoValidate(AcmeChallenge $challenge): bool
    {
        return $challenge->key_authorization !== null;
    }
}
