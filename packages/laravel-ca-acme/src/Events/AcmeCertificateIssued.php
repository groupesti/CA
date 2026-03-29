<?php

declare(strict_types=1);

namespace CA\Acme\Events;

use CA\Acme\Models\AcmeOrder;
use CA\Crt\Models\Certificate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcmeCertificateIssued
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly AcmeOrder $order,
        public readonly Certificate $certificate,
    ) {}
}
