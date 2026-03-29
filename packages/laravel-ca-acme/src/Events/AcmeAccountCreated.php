<?php

declare(strict_types=1);

namespace CA\Acme\Events;

use CA\Acme\Models\AcmeAccount;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcmeAccountCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly AcmeAccount $account,
    ) {}
}
