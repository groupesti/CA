<?php

declare(strict_types=1);

namespace CA\Key\Events;

use CA\Key\Models\Key;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class KeyRotated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Key $oldKey,
        public readonly Key $newKey,
    ) {}
}
