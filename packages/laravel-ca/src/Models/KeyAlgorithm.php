<?php

declare(strict_types=1);

namespace CA\Models;

class KeyAlgorithm extends Lookup
{
    protected static string $lookupType = 'key_algorithm';

    public const RSA_2048 = 'rsa-2048';
    public const RSA_4096 = 'rsa-4096';
    public const ECDSA_P256 = 'ecdsa-p256';
    public const ECDSA_P384 = 'ecdsa-p384';
    public const ECDSA_P521 = 'ecdsa-p521';
    public const ED25519 = 'ed25519';

    public function getKeySize(): ?int
    {
        return $this->meta('key_size');
    }

    public function getCurve(): ?string
    {
        return $this->meta('curve');
    }

    public function isRsa(): bool
    {
        return (bool) $this->meta('is_rsa', false);
    }

    public function isEc(): bool
    {
        return (bool) $this->meta('is_ec', false);
    }

    public function isEdDsa(): bool
    {
        return (bool) $this->meta('is_eddsa', false);
    }
}
