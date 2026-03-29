<?php

declare(strict_types=1);

namespace CA\Policy\Events;

use CA\Policy\Models\IssuanceRule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssuanceRuleCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly IssuanceRule $issuanceRule,
    ) {}
}
