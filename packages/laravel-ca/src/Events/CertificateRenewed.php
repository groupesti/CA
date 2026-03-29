<?php

declare(strict_types=1);

namespace CA\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CertificateRenewed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $oldCertificateId,
        public readonly string $newCertificateId,
        public readonly string $serialNumber,
    ) {}
}
