<?php

declare(strict_types=1);

namespace CA\Key\Contracts;

use CA\Models\ExportFormat;
use CA\Models\KeyAlgorithm;
use CA\Key\Models\Key;
use phpseclib3\Crypt\Common\PrivateKey;

interface KeyManagerInterface
{
    /**
     * Generate a new key pair.
     *
     * @param  array<string, mixed>  $params
     */
    public function generate(KeyAlgorithm $algorithm, array $params = [], ?string $tenantId = null): Key;

    /**
     * Import an existing key.
     *
     * @param  array<string, mixed>  $options
     */
    public function import(string $keyData, string $format, array $options = []): Key;

    /**
     * Export a key in the requested format.
     */
    public function export(Key $key, ExportFormat $format, ?string $passphrase = null): string;

    /**
     * Rotate a key, generating a new one with the same parameters.
     */
    public function rotate(Key $key): Key;

    /**
     * Destroy a key, marking it as destroyed.
     */
    public function destroy(Key $key): void;

    /**
     * Find a key by its SHA-256 fingerprint.
     */
    public function getByFingerprint(string $fingerprint): ?Key;

    /**
     * Decrypt the private key and return a phpseclib PrivateKey object.
     */
    public function decryptPrivateKey(Key $key, ?string $passphrase = null): PrivateKey;
}
