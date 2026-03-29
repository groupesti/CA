<?php

declare(strict_types=1);

namespace CA\Key\Services;

use CA\Models\ExportFormat;
use CA\Exceptions\InvalidKeyException;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\Common\PublicKey;

final class KeyExporter
{
    /**
     * Export a private key to PEM format.
     */
    public function toPem(PrivateKey $privateKey): string
    {
        return $privateKey->toString('PKCS8');
    }

    /**
     * Export a public key to PEM format.
     */
    public function publicToPem(PublicKey $publicKey): string
    {
        return $publicKey->toString('PKCS8');
    }

    /**
     * Export a private key to DER format.
     */
    public function toDer(PrivateKey $privateKey): string
    {
        $pem = $privateKey->toString('PKCS8');

        return $this->pemToDer($pem);
    }

    /**
     * Export a public key to DER format.
     */
    public function publicToDer(PublicKey $publicKey): string
    {
        $pem = $publicKey->toString('PKCS8');

        return $this->pemToDer($pem);
    }

    /**
     * Export a private key to encrypted PEM (PKCS#8 with passphrase).
     */
    public function toEncryptedPem(PrivateKey $privateKey, string $passphrase): string
    {
        return $privateKey->withPassword($passphrase)->toString('PKCS8');
    }

    /**
     * Export a key according to the given format.
     */
    public function export(
        PrivateKey $privateKey,
        ExportFormat $format,
        ?string $passphrase = null,
    ): string {
        return match ($format) {
            ExportFormat::PEM => $passphrase !== null
                ? $this->toEncryptedPem($privateKey, $passphrase)
                : $this->toPem($privateKey),
            ExportFormat::DER => $this->toDer($privateKey),
            default => throw new InvalidKeyException(
                "Unsupported export format: {$format->slug}"
            ),
        };
    }

    /**
     * Convert PEM-encoded data to raw DER bytes.
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
}
