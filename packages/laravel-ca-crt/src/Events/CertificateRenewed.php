<?php

declare(strict_types=1);

namespace CA\Crt\Events;

use CA\Crt\Models\Certificate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CertificateRenewed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Certificate $newCertificate,
        public readonly Certificate $previousCertificate,
    ) {}
}
