<?php

declare(strict_types=1);

namespace CA\DTOs;

use CA\Models\CertificateType;
use CA\Models\HashAlgorithm;

final readonly class CertificateOptions
{
    public function __construct(
        public CertificateType $type,
        public int $validityDays,
        public ?HashAlgorithm $hashAlgorithm = null,
        public array $keyUsage = [],
        public array $extendedKeyUsage = [],
        public ?array $subjectAlternativeNames = null,
        public ?bool $isCa = null,
        public ?int $pathLength = null,
        public array $customExtensions = [],
        public ?string $templateId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] instanceof CertificateType
                ? $data['type']
                : CertificateType::from($data['type']),
            validityDays: (int) $data['validity_days'],
            hashAlgorithm: isset($data['hash_algorithm'])
                ? ($data['hash_algorithm'] instanceof HashAlgorithm
                    ? $data['hash_algorithm']
                    : HashAlgorithm::from($data['hash_algorithm']))
                : null,
            keyUsage: $data['key_usage'] ?? [],
            extendedKeyUsage: $data['extended_key_usage'] ?? [],
            subjectAlternativeNames: $data['subject_alternative_names'] ?? null,
            isCa: $data['is_ca'] ?? null,
            pathLength: isset($data['path_length']) ? (int) $data['path_length'] : null,
            customExtensions: $data['custom_extensions'] ?? [],
            templateId: $data['template_id'] ?? null,
        );
    }
}
