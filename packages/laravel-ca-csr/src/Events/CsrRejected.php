<?php

declare(strict_types=1);

namespace CA\Csr\Events;

use CA\Csr\Models\Csr;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CsrRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Csr $csr,
        public readonly string $reason,
    ) {}
}
