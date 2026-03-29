<?php

declare(strict_types=1);

namespace CA\Crt\Events;

use CA\Crt\Models\Certificate;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CertificateExpiring
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Certificate $certificate,
        public readonly int $daysUntilExpiry,
    ) {}
}
