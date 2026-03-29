<?php

declare(strict_types=1);

namespace CA\OCSP\DTOs;

use CA\Models\RevocationReason;
use Carbon\Carbon;

readonly class CertStatusResult
{
    /**
     * @param  string  $status  One of 'good', 'revoked', 'unknown'.
     */
    public function __construct(
        public string $status,
        public ?Carbon $revocationTime = null,
        public ?RevocationReason $revocationReason = null,
        public Carbon $thisUpdate = new Carbon(),
        public ?Carbon $nextUpdate = null,
    ) {}
}
