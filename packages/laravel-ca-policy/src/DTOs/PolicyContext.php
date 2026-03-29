<?php

declare(strict_types=1);

namespace CA\Policy\DTOs;

use CA\DTOs\CertificateOptions;
use CA\DTOs\DistinguishedName;
use CA\Models\CertificateType;
use CA\Models\CertificateAuthority;

final readonly class PolicyContext
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public CertificateAuthority $ca,
        public CertificateOptions $options,
        public ?DistinguishedName $subject = null,
        public ?array $sans = null,
        public ?CertificateType $type = null,
        public array $metadata = [],
    ) {}
}
