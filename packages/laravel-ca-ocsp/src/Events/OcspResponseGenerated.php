<?php

declare(strict_types=1);

namespace CA\OCSP\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OcspResponseGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $caUuid,
        public readonly string $serial,
        public readonly string $status,
    ) {}
}
