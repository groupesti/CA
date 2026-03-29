<?php

declare(strict_types=1);

namespace CA\Policy\Contracts;

use CA\Policy\DTOs\PolicyContext;
use CA\Policy\DTOs\PolicyRuleResult;

interface PolicyRuleInterface
{
    public function evaluate(PolicyContext $context): PolicyRuleResult;

    public function getName(): string;

    public function getDescription(): string;

    public function getSeverity(): string;
}
