<?php

declare(strict_types=1);

namespace CA\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Lookup extends Model
{
    protected $table = 'ca_lookups';

    protected $fillable = [
        'type',
        'slug',
        'name',
        'description',
        'numeric_value',
        'metadata',
        'sort_order',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'metadata' => 'array',
        'numeric_value' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    protected static string $lookupType = '';

    protected static function booted(): void
    {
        if (static::$lookupType !== '') {
            static::addGlobalScope('type', function (Builder $builder) {
                $builder->where('type', static::$lookupType);
            });

            static::creating(function (self $model) {
                $model->type = static::$lookupType;
            });
        }
    }

    public static function fromSlug(string $slug): static
    {
        return cache()->remember(
            'ca_lookup:' . static::$lookupType . ':' . $slug,
            3600,
            fn () => static::where('slug', $slug)->firstOrFail(),
        );
    }

    public static function active(): Collection
    {
        return cache()->remember(
            'ca_lookup:' . static::$lookupType . ':active',
            3600,
            fn () => static::where('is_active', true)->orderBy('sort_order')->get(),
        );
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    public function is(string $slug): bool
    {
        return $this->slug === $slug;
    }

    public function __toString(): string
    {
        return $this->slug;
    }

    public static function clearCache(): void
    {
        cache()->forget('ca_lookup:' . static::$lookupType . ':active');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function delete(): ?bool
    {
        if ($this->is_system) {
            throw new \RuntimeException("Cannot delete system lookup entry: {$this->type}.{$this->slug}");
        }

        return parent::delete();
    }
}
