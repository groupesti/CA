<?php

declare(strict_types=1);

namespace CA\Encryption;

use CA\Contracts\EncryptionStrategyInterface;
use CA\Exceptions\CaException;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;

final class PassphraseEncryptionStrategy implements EncryptionStrategyInterface
{
    private const int IV_LENGTH = 16;
    private const int SALT_LENGTH = 16;
    private const int KEY_LENGTH = 32;
    private const int ITERATIONS = 100000;

    public function encrypt(string $plaintext, array $options = []): string
    {
        $passphrase = $options['passphrase'] ?? null;

        if ($passphrase === null || $passphrase === '') {
            throw new CaException('Passphrase is required for passphrase encryption strategy.');
        }

        try {
            $salt = Random::string(self::SALT_LENGTH);
            $iv = Random::string(self::IV_LENGTH);
            $key = $this->deriveKey($passphrase, $salt);

            $aes = new AES('cbc');
            $aes->setKey($key);
            $aes->setIV($iv);

            $encrypted = $aes->encrypt($plaintext);

            $payload = json_encode([
                'salt' => base64_encode($salt),
                'iv' => base64_encode($iv),
                'data' => base64_encode($encrypted),
                'iterations' => self::ITERATIONS,
            ], JSON_THROW_ON_ERROR);

            return base64_encode($payload);
        } catch (CaException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new CaException(
                "Passphrase encryption failed: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    public function decrypt(string $ciphertext, array $options = []): string
    {
        $passphrase = $options['passphrase'] ?? null;

        if ($passphrase === null || $passphrase === '') {
            throw new CaException('Passphrase is required for passphrase decryption strategy.');
        }

        try {
            $decoded = base64_decode($ciphertext, strict: true);

            if ($decoded === false) {
                throw new CaException('Invalid ciphertext format.');
            }

            $payload = json_decode($decoded, associative: true, flags: JSON_THROW_ON_ERROR);

            $salt = base64_decode($payload['salt'], strict: true);
            $iv = base64_decode($payload['iv'], strict: true);
            $data = base64_decode($payload['data'], strict: true);
            $iterations = $payload['iterations'] ?? self::ITERATIONS;

            if ($salt === false || $iv === false || $data === false) {
                throw new CaException('Invalid ciphertext payload components.');
            }

            $key = $this->deriveKey($passphrase, $salt, $iterations);

            $aes = new AES('cbc');
            $aes->setKey($key);
            $aes->setIV($iv);

            $decrypted = $aes->decrypt($data);

            if ($decrypted === false) {
                throw new CaException('Decryption failed - invalid passphrase or corrupted data.');
            }

            return $decrypted;
        } catch (CaException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new CaException(
                "Passphrase decryption failed: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    public function getStrategyName(): string
    {
        return 'passphrase';
    }

    private function deriveKey(string $passphrase, string $salt, int $iterations = self::ITERATIONS): string
    {
        return hash_pbkdf2(
            algo: 'sha256',
            password: $passphrase,
            salt: $salt,
            iterations: $iterations,
            length: self::KEY_LENGTH,
            binary: true,
        );
    }
}
