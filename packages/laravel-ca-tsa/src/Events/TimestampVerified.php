<?php

declare(strict_types=1);

namespace CA\Tsa\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TimestampVerified
{
    use Dispatchable;

    public function __construct(
        public readonly string $serial,
        public readonly bool $valid,
    ) {}
}
