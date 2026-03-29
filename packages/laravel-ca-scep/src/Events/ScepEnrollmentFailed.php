<?php

declare(strict_types=1);

namespace CA\Scep\Events;

use CA\Models\CertificateAuthority;
use CA\Scep\Models\ScepTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ScepEnrollmentFailed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly ScepTransaction $transaction,
        public readonly CertificateAuthority $ca,
        public readonly string $reason,
    ) {}
}
