<?php

declare(strict_types=1);

namespace CA\Policy\Events;

use CA\DTOs\CertificateOptions;
use CA\Models\CertificateAuthority;
use CA\Policy\DTOs\PolicyResult;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyEvaluated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly CertificateAuthority $ca,
        public readonly CertificateOptions $options,
        public readonly PolicyResult $result,
    ) {}
}
