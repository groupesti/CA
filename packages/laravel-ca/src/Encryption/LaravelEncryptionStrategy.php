<?php

declare(strict_types=1);

namespace CA\Encryption;

use CA\Contracts\EncryptionStrategyInterface;
use CA\Exceptions\CaException;
use Illuminate\Support\Facades\Crypt;

final class LaravelEncryptionStrategy implements EncryptionStrategyInterface
{
    public function encrypt(string $plaintext, array $options = []): string
    {
        try {
            return Crypt::encryptString($plaintext);
        } catch (\Throwable $e) {
            throw new CaException(
                "Laravel encryption failed: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    public function decrypt(string $ciphertext, array $options = []): string
    {
        try {
            return Crypt::decryptString($ciphertext);
        } catch (\Throwable $e) {
            throw new CaException(
                "Laravel decryption failed: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    public function getStrategyName(): string
    {
        return 'laravel';
    }
}
