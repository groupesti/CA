<?php

declare(strict_types=1);

namespace CA\Models;

class CertificateStatus extends Lookup
{
    protected static string $lookupType = 'certificate_status';

    public const ACTIVE = 'active';
    public const REVOKED = 'revoked';
    public const EXPIRED = 'expired';
    public const SUSPENDED = 'suspended';
    public const ON_HOLD = 'on_hold';

    public function isValid(): bool
    {
        return (bool) $this->meta('is_valid', false);
    }
}
