<?php

declare(strict_types=1);

namespace CA\Models;

class PolicyAction extends Lookup
{
    protected static string $lookupType = 'policy_action';

    public const DENY = 'deny';
    public const ALLOW = 'allow';
    public const WARN = 'warn';
    public const REQUIRE_APPROVAL = 'require_approval';
}
