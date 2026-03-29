<?php

declare(strict_types=1);

namespace CA\Acme\Contracts;

use CA\Acme\Models\AcmeChallenge;

interface ChallengeValidatorInterface
{
    /**
     * Validate the challenge.
     */
    public function validate(AcmeChallenge $challenge): bool;

    /**
     * Get the expected key authorization string for this challenge.
     */
    public function getExpectedKeyAuthorization(AcmeChallenge $challenge, string $accountThumbprint): string;
}
