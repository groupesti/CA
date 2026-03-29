<?php

declare(strict_types=1);

namespace CA\Key\Models;

use CA\Models\CertificateAuthority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Key extends Model
{
    use SoftDeletes;

    protected $table = 'ca_keys';

    protected $fillable = [
        'uuid',
        'ca_id',
        'tenant_id',
        'algorithm',
        'parameters',
        'public_key_pem',
        'private_key_encrypted',
        'encryption_strategy',
        'fingerprint_sha256',
        'status',
        'usage',
        'storage_path',
    ];

    protected $hidden = [
        'private_key_encrypted',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'uuid' => 'string',
            'parameters' => 'array',
        ];
    }

    /**
     * @return BelongsTo<CertificateAuthority, $this>
     */
    public function ca(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    /**
     * Scope to only active keys.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by tenant.
     */
    public function scopeByTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to filter by algorithm.
     */
    public function scopeByAlgorithm(Builder $query, string $algorithm): Builder
    {
        return $query->where('algorithm', $algorithm);
    }

    /**
     * Scope to filter by SHA-256 fingerprint.
     */
    public function scopeByFingerprint(Builder $query, string $fingerprint): Builder
    {
        return $query->where('fingerprint_sha256', $fingerprint);
    }
}
