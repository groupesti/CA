<?php

declare(strict_types=1);

namespace CA\Est\Events;

use CA\Crt\Models\Certificate;
use CA\Est\Models\EstEnrollment;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstServerKeyGenerated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly CertificateAuthority $ca,
        public readonly EstEnrollment $enrollment,
        public readonly Key $key,
        public readonly Certificate $certificate,
    ) {}
}
