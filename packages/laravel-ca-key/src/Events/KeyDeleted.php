<?php

declare(strict_types=1);

namespace CA\Key\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class KeyDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly string $fingerprint,
    ) {}
}
