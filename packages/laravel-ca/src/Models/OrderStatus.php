<?php

declare(strict_types=1);

namespace CA\Models;

class OrderStatus extends Lookup
{
    protected static string $lookupType = 'acme_order_status';

    public const PENDING = 'pending';
    public const READY = 'ready';
    public const PROCESSING = 'processing';
    public const VALID = 'valid';
    public const INVALID = 'invalid';
    public const EXPIRED = 'expired';
    public const REVOKED = 'revoked';
}
