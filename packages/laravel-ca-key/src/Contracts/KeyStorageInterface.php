<?php

declare(strict_types=1);

namespace CA\Key\Contracts;

use CA\Contracts\StorageDriverInterface;
use CA\Key\Models\Key;

interface KeyStorageInterface extends StorageDriverInterface
{
    /**
     * Store both encrypted private and public PEM for a key.
     */
    public function storeKey(Key $key, string $encryptedPrivate, string $publicPem): void;

    /**
     * Retrieve the encrypted private key data.
     */
    public function retrievePrivateKey(Key $key): string;

    /**
     * Retrieve the public key PEM.
     */
    public function retrievePublicKey(Key $key): string;
}
