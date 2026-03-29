<?php

declare(strict_types=1);

namespace CA\Models;

class KeyStatus extends Lookup
{
    protected static string $lookupType = 'key_status';

    public const ACTIVE = 'active';
    public const ROTATED = 'rotated';
    public const DESTROYED = 'destroyed';
    public const SUSPENDED = 'suspended';
    public const COMPROMISED = 'compromised';

    public function isUsable(): bool
    {
        return (bool) $this->meta('is_usable', false);
    }
}
