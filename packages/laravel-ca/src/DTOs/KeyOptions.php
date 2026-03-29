<?php

declare(strict_types=1);

namespace CA\DTOs;

use CA\Models\KeyAlgorithm;

final readonly class KeyOptions
{
    public function __construct(
        public KeyAlgorithm $algorithm,
        public ?string $passphrase = null,
        public ?string $encryptionStrategy = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            algorithm: $data['algorithm'] instanceof KeyAlgorithm
                ? $data['algorithm']
                : KeyAlgorithm::from($data['algorithm']),
            passphrase: $data['passphrase'] ?? null,
            encryptionStrategy: $data['encryption_strategy'] ?? null,
        );
    }
}
