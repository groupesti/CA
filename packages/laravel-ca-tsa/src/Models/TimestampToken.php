<?php

declare(strict_types=1);

namespace CA\Tsa\Models;

use CA\Crt\Models\Certificate;
use CA\Models\CertificateAuthority;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimestampToken extends Model
{
    use HasUuids;
    use BelongsToTenant;
    use Auditable;

    protected $table = 'ca_tsa_tokens';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'ca_id',
        'tenant_id',
        'serial_number',
        'hash_algorithm',
        'message_imprint',
        'policy_oid',
        'gen_time',
        'nonce',
        'accuracy',
        'tsr_der',
        'signing_certificate_id',
        'ip_address',
    ];

    protected $hidden = [
        'tsr_der',
    ];

    protected function casts(): array
    {
        return [
            'gen_time' => 'datetime',
            'accuracy' => 'array',
        ];
    }

    // ---- Relationships ----

    public function certificateAuthority(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function signingCertificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'signing_certificate_id');
    }

    // ---- Scopes ----

    public function scopeForCa(Builder $query, string $caId): Builder
    {
        return $query->where('ca_id', $caId);
    }

    public function scopeBySerial(Builder $query, string $serialNumber): Builder
    {
        return $query->where('serial_number', $serialNumber);
    }

    public function scopeRecent(Builder $query, int $limit = 50): Builder
    {
        return $query->orderByDesc('gen_time')->limit($limit);
    }
}
