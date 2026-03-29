<?php

declare(strict_types=1);

namespace CA\Models;

class ScepPkiStatus extends Lookup
{
    protected static string $lookupType = 'scep_pki_status';

    public const SUCCESS = 'success';
    public const FAILURE = 'failure';
    public const PENDING = 'pending';
}
