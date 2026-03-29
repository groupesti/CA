<?php

declare(strict_types=1);

namespace CA\Policy\Models;

use CA\Models\CertificateType;
use CA\Models\CertificateAuthority;
use CA\Models\PolicyAction;
use CA\Models\PolicySeverity;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuanceRule extends Model
{
    use HasUuids;
    use Auditable;
    use BelongsToTenant;

    protected $table = 'ca_issuance_rules';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'ca_id',
        'policy_id',
        'tenant_id',
        'name',
        'description',
        'rule_class',
        'parameters',
        'priority',
        'severity',
        'action_on_failure',
        'enabled',
        'applies_to_types',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'priority' => 'integer',
            'severity' => 'string',
            'action_on_failure' => 'string',
            'enabled' => 'boolean',
            'applies_to_types' => 'array',
        ];
    }

    // ---- Relationships ----

    public function ca(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(CertificatePolicy::class, 'policy_id');
    }

    // ---- Scopes ----

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeForType(Builder $query, CertificateType $type): Builder
    {
        return $query->where(function (Builder $q) use ($type): void {
            $q->whereNull('applies_to_types')
                ->orWhereJsonContains('applies_to_types', $type->slug);
        });
    }

    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority', 'asc');
    }
}
