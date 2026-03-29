<?php

declare(strict_types=1);

namespace CA\Events;

use CA\Models\CertificateAuthority;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CertificateIssued
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $certificateId,
        public readonly CertificateAuthority $issuingCa,
        public readonly string $serialNumber,
    ) {}
}
