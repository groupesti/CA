<?php

declare(strict_types=1);

namespace CA\Events;

use CA\Models\RevocationReason;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CertificateRevoked
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $certificateId,
        public readonly string $serialNumber,
        public readonly RevocationReason $reason,
    ) {}
}
