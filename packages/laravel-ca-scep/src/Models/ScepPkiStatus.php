<?php

declare(strict_types=1);

namespace CA\Scep\Models;

use CA\Models\Lookup;

class ScepPkiStatus extends Lookup
{
    protected static string $lookupType = 'scep_pki_status';

    public const SUCCESS = 'success';
    public const FAILURE = 'failure';
    public const PENDING = 'pending';

    public function getScepCode(): int
    {
        return $this->numeric_value ?? 0;
    }
}
