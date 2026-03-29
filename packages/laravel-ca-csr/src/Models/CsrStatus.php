<?php

declare(strict_types=1);

namespace CA\Csr\Models;

use CA\Models\Lookup;

class CsrStatus extends Lookup
{
    protected static string $lookupType = 'csr_status';

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const SIGNED = 'signed';
    public const EXPIRED = 'expired';

    public function isPending(): bool
    {
        return (bool) $this->meta('is_pending', false);
    }

    public function isActionable(): bool
    {
        return (bool) $this->meta('is_actionable', false);
    }
}
