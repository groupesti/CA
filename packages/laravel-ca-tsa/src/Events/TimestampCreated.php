<?php

declare(strict_types=1);

namespace CA\Tsa\Events;

use CA\Tsa\Models\TimestampToken;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimestampCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly TimestampToken $token,
    ) {}
}
