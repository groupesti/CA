<?php

declare(strict_types=1);

namespace CA\Models;

class CsrStatus extends Lookup
{
    protected static string $lookupType = 'csr_status';

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const SIGNED = 'signed';

    public function isPending(): bool
    {
        return $this->slug === self::PENDING;
    }

    public function isActionable(): bool
    {
        return $this->slug === self::PENDING;
    }
}
