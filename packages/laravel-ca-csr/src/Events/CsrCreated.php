<?php

declare(strict_types=1);

namespace CA\Csr\Events;

use CA\Csr\Models\Csr;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CsrCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Csr $csr,
    ) {}
}
