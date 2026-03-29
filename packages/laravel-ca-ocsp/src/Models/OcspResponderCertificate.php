<?php

declare(strict_types=1);

namespace CA\OCSP\Models;

use CA\Crt\Models\Certificate;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcspResponderCertificate extends Model
{
    use HasUuids;
    use BelongsToTenant;

    protected $table = 'ca_ocsp_responder_certificates';

    protected $fillable = [
        'ca_id',
        'tenant_id',
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
