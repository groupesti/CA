<?php

declare(strict_types=1);

namespace CA\Tsa\Models;

use CA\Crt\Models\Certificate;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TsaCertificate extends Model
{
    protected $table = 'ca_tsa_certificates';

    protected $fillable = [
        'ca_id',
        'certificate_id',
        'key_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ---- Relationships ----

    public function certificateAuthority(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class, 'key_id');
    }

    // ---- Scopes ----

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCa(Builder $query, string $caId): Builder
    {
        return $query->where('ca_id', $caId);
    }
}
