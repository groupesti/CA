<?php

declare(strict_types=1);

namespace CA\Key\Services;

use CA\Contracts\EncryptionStrategyInterface;
use CA\Models\ExportFormat;
use CA\Models\KeyAlgorithm;
use CA\Exceptions\InvalidKeyException;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Models\KeyStatus;
use CA\Key\Events\KeyDeleted;
use CA\Key\Events\KeyGenerated;
use CA\Key\Events\KeyRotated;
use CA\Key\Models\Key;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

final class KeyManager implements KeyManagerInterface
{
    public function __construct(
        private readonly KeyGenerator $keyGenerator,
        private readonly KeyExporter $keyExporter,
        private readonly EncryptionStrategyInterface $encryptionStrategy,
    ) {}

    public function generate(
        KeyAlgorithm $algorithm,
        array $params = [],
        ?string $tenantId = null,
    ): Key {
        $generationParams = $this->buildGenerationParams($algorithm, $params);

        $keyPair = $this->keyGenerator->generate($algorithm, $generationParams);

        $publicPem = $keyPair['publicKey']->toString('PKCS8');
        $privatePem = $keyPair['privateKey']->toString('PKCS8');

        $encryptedPrivate = $this->encryptionStrategy->encrypt($privatePem);

        $fingerprint = $this->computeFingerprint($publicPem);

        $key = Key::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'ca_id' => $params['ca_id'] ?? null,
            'tenant_id' => $tenantId,
            'algorithm' => (string) $algorithm,
            'parameters' => $generationParams,
            'public_key_pem' => $publicPem,
            'private_key_encrypted' => $encryptedPrivate,
            'encryption_strategy' => $this->encryptionStrategy->getStrategyName(),
            'fingerprint_sha256' => $fingerprint,
            'status' => KeyStatus::ACTIVE,
            'usage' => $params['usage'] ?? 'certificate',
            'storage_path' => $params['storage_path'] ?? null,
        ]);

        event(new KeyGenerated($key));

        return $key;
    }

    public function import(string $keyData, string $format, array $options = []): Key
    {
        $privateKey = $this->loadPrivateKey($keyData, $options['passphrase'] ?? null);
        $publicKey = $privateKey->getPublicKey();

        $publicPem = $publicKey->toString('PKCS8');
        $privatePem = $privateKey->toString('PKCS8');

        $encryptedPrivate = $this->encryptionStrategy->encrypt($privatePem);
        $fingerprint = $this->computeFingerprint($publicPem);

        $algorithmSlug = $this->detectAlgorithmSlug($privateKey);

        $key = Key::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'ca_id' => $options['ca_id'] ?? null,
            'tenant_id' => $options['tenant_id'] ?? null,
            'algorithm' => $algorithmSlug,
            'parameters' => $options['parameters'] ?? [],
            'public_key_pem' => $publicPem,
            'private_key_encrypted' => $encryptedPrivate,
            'encryption_strategy' => $this->encryptionStrategy->getStrategyName(),
            'fingerprint_sha256' => $fingerprint,
            'status' => KeyStatus::ACTIVE,
            'usage' => $options['usage'] ?? 'certificate',
            'storage_path' => $options['storage_path'] ?? null,
        ]);

        event(new KeyGenerated($key));

        return $key;
    }

    public function export(Key $key, ExportFormat $format, ?string $passphrase = null): string
    {
        $privateKey = $this->decryptPrivateKey($key);

        return $this->keyExporter->export($privateKey, $format, $passphrase);
    }

    public function rotate(Key $key): Key
    {
        $algorithm = KeyAlgorithm::fromSlug($key->algorithm);
        $params = array_merge($key->parameters ?? [], [
            'ca_id' => $key->ca_id,
            'usage' => $key->usage,
        ]);

        $newKey = $this->generate($algorithm, $params, $key->tenant_id);

        $key->update(['status' => KeyStatus::ROTATED]);

        event(new KeyRotated(oldKey: $key->fresh(), newKey: $newKey));

        return $newKey;
    }

    public function destroy(Key $key): void
    {
        $fingerprint = $key->fingerprint_sha256;

        $key->update([
            'status' => KeyStatus::DESTROYED,
            'private_key_encrypted' => '',
        ]);

        $key->delete();

        event(new KeyDeleted(fingerprint: $fingerprint));
    }

    public function getByFingerprint(string $fingerprint): ?Key
    {
        return Key::where('fingerprint_sha256', $fingerprint)->first();
    }

    public function decryptPrivateKey(Key $key, ?string $passphrase = null): PrivateKey
    {
        $encryptedData = $key->private_key_encrypted;

        if (empty($encryptedData)) {
            throw new InvalidKeyException('Private key data is empty or has been destroyed.');
        }

        $privatePem = $this->encryptionStrategy->decrypt($encryptedData);

        return $this->loadPrivateKey($privatePem, $passphrase);
    }

    /**
     * Compute SHA-256 fingerprint of the public key DER encoding.
     */
    private function computeFingerprint(string $publicPem): string
    {
        $der = $this->pemToDer($publicPem);
        $hash = hash('sha256', $der);

        return implode(':', str_split($hash, 2));
    }

    /**
     * Convert PEM to DER.
     */
    private function pemToDer(string $pem): string
    {
        $pem = preg_replace('/-----[A-Z\s]+-----/', '', $pem);
        $pem = preg_replace('/\s+/', '', (string) $pem);

        $der = base64_decode((string) $pem, true);

        if ($der === false) {
            throw new InvalidKeyException('Failed to decode PEM to DER.');
        }

        return $der;
    }

    /**
     * Load a private key from PEM or DER data.
     */
    private function loadPrivateKey(string $keyData, ?string $passphrase = null): PrivateKey
    {
        try {
            $key = $passphrase !== null
                ? PublicKeyLoader::loadPrivateKey($keyData, $passphrase)
                : PublicKeyLoader::loadPrivateKey($keyData);

            if (! $key instanceof PrivateKey) {
                throw new InvalidKeyException('Provided data is not a valid private key.');
            }

            return $key;
        } catch (InvalidKeyException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new InvalidKeyException(
                'Failed to load private key: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Detect the algorithm slug of a loaded private key.
     */
    private function detectAlgorithmSlug(PrivateKey $key): string
    {
        if ($key instanceof RSA\PrivateKey) {
            $bits = $key->getLength();

            return match (true) {
                $bits <= 2048 => KeyAlgorithm::RSA_2048,
                default => KeyAlgorithm::RSA_4096,
            };
        }

        if ($key instanceof EC\PrivateKey) {
            $curve = $key->getCurve();

            return match ($curve) {
                'secp256r1', 'prime256v1', 'nistp256' => KeyAlgorithm::ECDSA_P256,
                'secp384r1', 'nistp384' => KeyAlgorithm::ECDSA_P384,
                'secp521r1', 'nistp521' => KeyAlgorithm::ECDSA_P521,
                'Ed25519' => KeyAlgorithm::ED25519,
                default => throw new InvalidKeyException("Unsupported EC curve: {$curve}"),
            };
        }

        throw new InvalidKeyException('Unable to detect key algorithm.');
    }

    /**
     * Build generation parameters from algorithm and user params.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function buildGenerationParams(KeyAlgorithm $algorithm, array $params): array
    {
        if ($algorithm->isRsa()) {
            return [
                'bits' => $params['bits'] ?? $algorithm->getKeySize(),
            ];
        }

        if ($algorithm->isEc() || $algorithm->isEdDsa()) {
            return [
                'curve' => $params['curve'] ?? $algorithm->getCurve(),
            ];
        }

        return $params;
    }
}
