<?php

declare(strict_types=1);

namespace CA\Key\Models;

use CA\Models\Lookup;

class KeyStatus extends Lookup
{
    protected static string $lookupType = 'key_status';

    public const ACTIVE = 'active';
    public const ROTATED = 'rotated';
    public const COMPROMISED = 'compromised';
    public const DESTROYED = 'destroyed';
    public const EXPIRED = 'expired';
}
