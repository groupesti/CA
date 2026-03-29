<?php

declare(strict_types=1);

namespace CA\Traits;

use CA\Contracts\TenantResolverInterface;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        if (!config('ca.tenant.enabled', false)) {
            return;
        }

        static::addGlobalScope('tenant', function (Builder $builder): void {
            $resolver = app()->bound(TenantResolverInterface::class)
                ? app(TenantResolverInterface::class)
                : null;

            if ($resolver === null) {
                return;
            }

            $tenantId = $resolver->resolve();

            if ($tenantId !== null) {
                $column = config('ca.tenant.column', 'tenant_id');
                $builder->where($builder->getModel()->getTable() . '.' . $column, $tenantId);
            }
        });

        static::creating(function ($model): void {
            if ($model->tenant_id !== null) {
                return;
            }

            $resolver = app()->bound(TenantResolverInterface::class)
                ? app(TenantResolverInterface::class)
                : null;

            if ($resolver !== null) {
                $model->tenant_id = $resolver->resolve();
            }
        });
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        $column = config('ca.tenant.column', 'tenant_id');

        return $query->where($this->getTable() . '.' . $column, $tenantId);
    }
}
