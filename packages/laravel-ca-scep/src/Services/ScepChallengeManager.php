<?php

declare(strict_types=1);

namespace CA\Scep\Services;

use CA\Models\CertificateAuthority;
use CA\Scep\Models\ScepChallengePassword;
use Illuminate\Support\Str;

final class ScepChallengeManager
{
    /**
     * Generate a challenge password for SCEP enrollment.
     *
     * Returns the plaintext password. The hash is stored in the database.
     */
    public function generate(
        CertificateAuthority $ca,
        ?string $purpose = null,
        ?int $ttlSeconds = null,
    ): string {
        $ttl = $ttlSeconds ?? (int) config('ca-scep.challenge_password_ttl', 3600);
        $password = Str::random(32);

        ScepChallengePassword::create([
            'ca_id' => $ca->id,
            'password_hash' => hash('sha256', $password),
            'purpose' => $purpose,
            'used' => false,
            'expires_at' => now()->addSeconds($ttl),
            'created_at' => now(),
        ]);

        return $password;
    }

    /**
     * Validate a challenge password against stored hashes.
     *
     * If valid, marks the password as used.
     */
    public function validate(CertificateAuthority $ca, string $password): bool
    {
        $hash = hash('sha256', $password);

        $challenge = ScepChallengePassword::query()
            ->forCa($ca->id)
            ->valid()
            ->unused()
            ->where('password_hash', $hash)
            ->first();

        if ($challenge === null) {
            return false;
        }

        $challenge->markUsed();

        return true;
    }

    /**
     * Check if a challenge password is valid without marking it as used.
     */
    public function isValid(CertificateAuthority $ca, string $password): bool
    {
        $hash = hash('sha256', $password);

        return ScepChallengePassword::query()
            ->forCa($ca->id)
            ->valid()
            ->unused()
            ->where('password_hash', $hash)
            ->exists();
    }

    /**
     * Clean up expired challenge passwords.
     */
    public function cleanupExpired(): int
    {
        return ScepChallengePassword::query()
            ->where('expires_at', '<', now())
            ->delete();
    }
}
