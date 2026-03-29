<?php

declare(strict_types=1);

namespace CA\Acme\Models;

use CA\Models\Lookup;

class ChallengeStatus extends Lookup
{
    protected static string $lookupType = 'acme_challenge_status';

    public const PENDING = 'pending';
    public const PROCESSING = 'processing';
    public const VALID = 'valid';
    public const INVALID = 'invalid';
}
