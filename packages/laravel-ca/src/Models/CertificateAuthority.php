<?php

declare(strict_types=1);

namespace CA\Models;

use CA\Contracts\CertificateAuthorityInterface;
use CA\Models\CertificateStatus;
use CA\Models\CertificateType;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use CA\Key\Models\Key;
use Illuminate\Support\Collection;

class CertificateAuthority extends Model implements CertificateAuthorityInterface
{
    use HasUuids;
    use SoftDeletes;
    use Auditable;
    use BelongsToTenant;

    protected $table = 'certificate_authorities';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'type',
        'status',
        'subject_dn',
        'serial_number',
        'key_algorithm',
        'hash_algorithm',
        'path_length',
        'not_before',
        'not_after',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'subject_dn' => 'array',
            'metadata' => 'array',
            'not_before' => 'datetime',
            'not_after' => 'datetime',
            'path_length' => 'integer',
        ];
    }

    // ---- Relationships ----

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(CertificateTemplate::class, 'certificate_authority_id');
    }

    public function keys(): HasMany
    {
        return $this->hasMany(Key::class, 'ca_id');
    }

    public function activeKey(): ?Key
    {
        return $this->keys()->where('status', 'active')->first();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'subject_id')
            ->where('subject_type', static::class);
    }

    // ---- Scopes ----

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CertificateStatus::ACTIVE);
    }

    // ---- Interface Implementation ----

    public function getId(): string
    {
        return $this->id;
    }

    public function getTenantId(): ?string
    {
        return $this->tenant_id;
    }

    public function getSubjectDN(): array
    {
        return $this->subject_dn ?? [];
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function getParent(): ?CertificateAuthorityInterface
    {
        return $this->parent;
    }

    public function getChildren(): Collection
    {
        return $this->children()->get();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getChainDepth(): int
    {
        $depth = 0;
        $current = $this;

        while ($current->parent_id !== null) {
            $depth++;
            $current = $current->parent;

            if ($current === null) {
                break;
            }
        }

        return $depth;
    }
}
