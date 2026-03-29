<?php

declare(strict_types=1);

namespace CA\Models;

class AuthorizationStatus extends Lookup
{
    protected static string $lookupType = 'acme_authorization_status';

    public const PENDING = 'pending';
    public const VALID = 'valid';
    public const INVALID = 'invalid';
    public const DEACTIVATED = 'deactivated';
    public const EXPIRED = 'expired';
    public const REVOKED = 'revoked';
}
