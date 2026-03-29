<?php

declare(strict_types=1);

namespace CA\Policy\Events;

use CA\Models\CertificateAuthority;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyViolation
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly CertificateAuthority $ca,
        public readonly string $rule,
        public readonly string $message,
        public readonly string $severity,
    ) {}
}
