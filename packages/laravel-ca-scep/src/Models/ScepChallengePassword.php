<?php

declare(strict_types=1);

namespace CA\Scep\Models;

use CA\Models\CertificateAuthority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScepChallengePassword extends Model
{
    public $timestamps = false;

    protected $table = 'ca_scep_challenge_passwords';

    protected $fillable = [
        'ca_id',
        'password_hash',
        'purpose',
        'used',
        'used_at',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    // ---- Relationships ----

    public function ca(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    // ---- Scopes ----

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>=', now());
    }

    public function scopeUnused(Builder $query): Builder
    {
        return $query->where('used', false);
    }

    public function scopeForCa(Builder $query, string $caId): Builder
    {
        return $query->where('ca_id', $caId);
    }

    // ---- Helpers ----

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used === true;
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }

    public function markUsed(): void
    {
        $this->update([
            'used' => true,
            'used_at' => now(),
        ]);
    }
}
