<?php

declare(strict_types=1);

namespace CA\Policy\Models;

use CA\Models\CertificateAuthority;
use CA\Models\NameType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NameConstraint extends Model
{
    protected $table = 'ca_name_constraints';

    protected $fillable = [
        'policy_id',
        'ca_id',
        'type',
        'name_type',
        'value',
        'min_subtree',
        'max_subtree',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'name_type' => 'string',
            'min_subtree' => 'integer',
            'max_subtree' => 'integer',
            'enabled' => 'boolean',
        ];
    }

    // ---- Relationships ----

    public function policy(): BelongsTo
    {
        return $this->belongsTo(CertificatePolicy::class, 'policy_id');
    }

    public function ca(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    // ---- Scopes ----

    public function scopePermitted(Builder $query): Builder
    {
        return $query->where('type', 'permitted');
    }

    public function scopeExcluded(Builder $query): Builder
    {
        return $query->where('type', 'excluded');
    }

    public function scopeForType(Builder $query, string $nameType): Builder
    {
        return $query->where('name_type', $nameType);
    }
}
