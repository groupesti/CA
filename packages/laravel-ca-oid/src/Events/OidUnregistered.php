<?php

declare(strict_types=1);

namespace CA\Oid\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OidUnregistered
{
    use Dispatchable;

    public function __construct(
        public readonly string $oid,
    ) {}
}
