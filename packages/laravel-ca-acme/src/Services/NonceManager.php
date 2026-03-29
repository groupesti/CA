<?php

declare(strict_types=1);

namespace CA\Acme\Services;

use CA\Acme\Models\AcmeNonce;

class NonceManager
{
    /**
     * Generate a new cryptographically random nonce and store it.
     */
    public function generate(): string
    {
        $nonce = JwsVerifier::base64UrlEncode(random_bytes(32));

        AcmeNonce::create([
            'nonce' => $nonce,
            'expires_at' => now()->addSeconds((int) config('ca-acme.nonce_ttl_seconds', 3600)),
            'used' => false,
        ]);

        return $nonce;
    }

    /**
     * Validate and consume a nonce (single-use).
     */
    public function validate(string $nonce): bool
    {
        $record = AcmeNonce::where('nonce', $nonce)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($record === null) {
            return false;
        }

        $record->update(['used' => true]);

        return true;
    }

    /**
     * Remove expired and used nonces.
     */
    public function cleanup(): void
    {
        AcmeNonce::where('expires_at', '<', now())
            ->orWhere('used', true)
            ->delete();
    }
}
