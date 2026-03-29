<?php

declare(strict_types=1);

namespace CA\Csr\Models;

use CA\Models\CsrStatus;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use CA\Models\CertificateTemplate;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Csr extends Model
{
    use HasUuids;
    use Auditable;
    use BelongsToTenant;

    protected $table = 'ca_csrs';

    protected $fillable = [
        'uuid',
        'ca_id',
        'tenant_id',
        'key_id',
        'template_id',
        'subject_dn',
        'san',
        'csr_pem',
        'status',
        'requested_by',
        'approved_by',
        'rejection_reason',
        'expires_at',
    ];

    protected $hidden = [
        'csr_pem',
    ];

    protected function casts(): array
    {
        return [
            'subject_dn' => 'array',
            'san' => 'array',
            'status' => 'string',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Generate a unique UUID for the model.
     *
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    // ---- Scopes ----

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CsrStatus::PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CsrStatus::APPROVED);
    }

    public function scopeForTemplate(Builder $query, string $templateId): Builder
    {
        return $query->where('template_id', $templateId);
    }

    public function scopeForCa(Builder $query, string $caId): Builder
    {
        return $query->where('ca_id', $caId);
    }

    // ---- Helpers ----

    public function isPending(): bool
    {
        return $this->status === CsrStatus::PENDING;
    }

    public function isActionable(): bool
    {
        return $this->status === CsrStatus::PENDING;
    }
}
