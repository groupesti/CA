<?php

declare(strict_types=1);

namespace CA\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CertificateExpiring
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $certificateId,
        public readonly string $serialNumber,
        public readonly int $daysUntilExpiry,
    ) {}
}
