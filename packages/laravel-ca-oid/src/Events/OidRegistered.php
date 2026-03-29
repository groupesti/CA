<?php

declare(strict_types=1);

namespace CA\Oid\Events;

use CA\Oid\Models\OidEntry;
use Illuminate\Foundation\Events\Dispatchable;

class OidRegistered
{
    use Dispatchable;

    public function __construct(
        public readonly OidEntry $oid,
    ) {}
}
