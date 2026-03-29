<?php

declare(strict_types=1);

namespace CA\Events;

use CA\Models\CertificateAuthority;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CaDeleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly CertificateAuthority $certificateAuthority,
    ) {}
}
