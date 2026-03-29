<?php

declare(strict_types=1);

namespace CA\Crt\Models;

use CA\Csr\Models\Csr;
use CA\Models\CertificateStatus;
use CA\Models\CertificateType;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use CA\Models\CertificateTemplate;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use CA\Traits\HasCertificateAuthority;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificate extends Model
{
    use HasUuids;
    use SoftDeletes;
    use BelongsToTenant;
    use HasCertificateAuthority;
    use Auditable;

    protected $table = 'ca_certificates';

    protected $fillable = [
        'uuid',
        'ca_id',
        'tenant_id',
        'key_id',
        'csr_id',
        'issuer_certificate_id',
        'template_id',
        'serial_number',
        'type',
        'subject_dn',
        'san',
        'certificate_pem',
        'certificate_der',
        'fingerprint_sha256',
        'status',
        'revocation_reason',
        'revoked_at',
        'not_before',
        'not_after',
        'key_usage',
        'extended_key_usage',
        'ms_template_oid',
        'ms_template_name',
        'metadata',
    ];

    protected $hidden = [
        'certificate_der',
    ];

    protected function casts(): array
    {
        return [
            'subject_dn' => 'array',
            'san' => 'array',
            'type' => 'string',
            'status' => 'string',
            'key_usage' => 'array',
            'extended_key_usage' => 'array',
            'metadata' => 'array',
            'not_before' => 'datetime',
            'not_after' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    // ---- Relationships ----

    public function certificateAuthority(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class, 'key_id');
    }

    public function csr(): BelongsTo
    {
        return $this->belongsTo(Csr::class, 'csr_id');
    }

    public function issuerCertificate(): BelongsTo
    {
        return $this->belongsTo(self::class, 'issuer_certificate_id');
    }

    public function issuedCertificates(): HasMany
    {
        return $this->hasMany(self::class, 'issuer_certificate_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function chains(): HasMany
    {
        return $this->hasMany(CertificateChain::class, 'certificate_id');
    }

    // ---- Scopes ----

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CertificateStatus::ACTIVE);
    }

    public function scopeExpiring(Builder $query, int $days): Builder
    {
        return $query
            ->where('status', CertificateStatus::ACTIVE)
            ->where('not_after', '<=', Carbon::now()->addDays($days))
            ->where('not_after', '>', Carbon::now());
    }

    public function scopeByType(Builder $query, CertificateType $type): Builder
    {
        return $query->where('type', $type->slug);
    }

    public function scopeBySerial(Builder $query, string $serialNumber): Builder
    {
        return $query->where('serial_number', $serialNumber);
    }

    public function scopeForCa(Builder $query, string $caId): Builder
    {
        return $query->where('ca_id', $caId);
    }

    // ---- Helpers ----

    public function isExpired(): bool
    {
        return $this->not_after !== null && $this->not_after->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->status === CertificateStatus::REVOKED;
    }

    public function isCa(): bool
    {
        return $this->type->isCa();
    }

    public function daysUntilExpiry(): int
    {
        if ($this->not_after === null) {
            return 0;
        }

        return (int) max(0, Carbon::now()->diffInDays($this->not_after, absolute: false));
    }
}
