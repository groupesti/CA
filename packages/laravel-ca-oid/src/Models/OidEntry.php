<?php

declare(strict_types=1);

namespace CA\Oid\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OidEntry extends Model
{
    protected $table = 'ca_oids';

    protected $fillable = [
        'oid',
        'name',
        'short_name',
        'description',
        'category',
        'is_standard',
        'parent_oid',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_standard' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function scopeStandard(Builder $query): Builder
    {
        return $query->where('is_standard', true);
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_standard', false);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('oid', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('short_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
