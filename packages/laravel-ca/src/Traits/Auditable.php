<?php

declare(strict_types=1);

namespace CA\Traits;

use CA\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model): void {
            self::recordAudit($model, 'created');
        });

        static::updated(function ($model): void {
            self::recordAudit($model, 'updated', [
                'changed' => $model->getChanges(),
                'original' => array_intersect_key($model->getOriginal(), $model->getChanges()),
            ]);
        });

        static::deleted(function ($model): void {
            self::recordAudit($model, 'deleted');
        });
    }

    public function getAuditAction(): string
    {
        return 'unknown';
    }

    public function getAuditMetadata(): array
    {
        return [
            'model' => static::class,
            'id' => $this->getKey(),
            'attributes' => $this->attributesToArray(),
        ];
    }

    private static function recordAudit(mixed $model, string $action, array $extraMetadata = []): void
    {
        $metadata = method_exists($model, 'getAuditMetadata')
            ? array_merge($model->getAuditMetadata(), $extraMetadata)
            : $extraMetadata;

        $actor = null;
        $actorType = null;
        $actorId = null;

        if (function_exists('auth') && auth()->check()) {
            $actor = auth()->user();
            $actorType = $actor ? get_class($actor) : null;
            $actorId = $actor?->getAuthIdentifier();
        }

        try {
            AuditLog::create([
                'tenant_id' => $model->tenant_id ?? null,
                'action' => static::class . '.' . $action,
                'subject_type' => static::class,
                'subject_id' => $model->getKey(),
                'actor_type' => $actorType,
                'actor_id' => $actorId ? (string) $actorId : null,
                'metadata' => $metadata,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'performed_at' => now(),
            ]);
        } catch (\Throwable) {
            // Silently fail to avoid breaking the main operation
            // Audit logging should never prevent core functionality
        }
    }
}
