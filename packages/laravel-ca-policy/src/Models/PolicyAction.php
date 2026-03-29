<?php

declare(strict_types=1);

namespace CA\Policy\Models;

use CA\Models\Lookup;

class PolicyAction extends Lookup
{
    protected static string $lookupType = 'policy_action';

    public const ALLOW = 'allow';
    public const DENY = 'deny';
    public const REQUIRE_APPROVAL = 'require_approval';
}
