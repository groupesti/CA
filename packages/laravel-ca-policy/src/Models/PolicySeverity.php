<?php

declare(strict_types=1);

namespace CA\Policy\Models;

use CA\Models\Lookup;

class PolicySeverity extends Lookup
{
    protected static string $lookupType = 'policy_severity';

    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const INFO = 'info';
}
