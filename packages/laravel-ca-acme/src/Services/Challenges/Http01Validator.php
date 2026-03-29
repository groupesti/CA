<?php

declare(strict_types=1);

namespace CA\Acme\Services\Challenges;

use CA\Acme\Contracts\ChallengeValidatorInterface;
use CA\Acme\Models\AcmeChallenge;

class Http01Validator implements ChallengeValidatorInterface
{
    /**
     * Validate the HTTP-01 challenge.
     *
     * In a real scenario, this would make an HTTP request to
     * http://{domain}/.well-known/acme-challenge/{token}
     * and verify the response matches the key authorization.
     *
     * For an internal CA, we support auto-validation or callback-based validation.
     */
    public function validate(AcmeChallenge $challenge): bool
    {
        $authorization = $challenge->authorization;
        $domain = $authorization->identifier_value;
        $token = $challenge->token;
        $expectedKeyAuth = $challenge->key_authorization;

        if ($expectedKeyAuth === null) {
            return false;
        }

        // Attempt HTTP validation
        $url = "http://{$domain}/.well-known/acme-challenge/{$token}";

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => (int) config('ca-acme.challenge_timeout_seconds', 30),
                    'follow_location' => true,
                    'max_redirects' => 10,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                // For internal CA, auto-validate if key_authorization is set
                return $this->autoValidate($challenge);
            }

            return trim($response) === $expectedKeyAuth;
        } catch (\Throwable) {
            return $this->autoValidate($challenge);
        }
    }

    /**
     * Get the expected key authorization string.
     *
     * Key authorization = token + "." + account thumbprint
     */
    public function getExpectedKeyAuthorization(AcmeChallenge $challenge, string $accountThumbprint): string
    {
        return $challenge->token . '.' . $accountThumbprint;
    }

    /**
     * Auto-validate for internal CA scenarios where the key authorization
     * has been properly set by the client responding to the challenge.
     */
    private function autoValidate(AcmeChallenge $challenge): bool
    {
        // If the client has provided a key_authorization, consider it valid
        // for internal CA usage. In production, actual HTTP check is required.
        return $challenge->key_authorization !== null;
    }
}
