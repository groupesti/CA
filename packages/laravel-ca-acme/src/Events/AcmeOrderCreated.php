<?php

declare(strict_types=1);

namespace CA\Acme\Events;

use CA\Acme\Models\AcmeOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcmeOrderCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly AcmeOrder $order,
    ) {}
}
