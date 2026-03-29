<?php

declare(strict_types=1);

namespace CA\Contracts;

interface EncryptionStrategyInterface
{
    public function encrypt(string $plaintext, array $options = []): string;

    public function decrypt(string $ciphertext, array $options = []): string;

    public function getStrategyName(): string;
}
