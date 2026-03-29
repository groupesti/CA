<?php

declare(strict_types=1);

namespace CA\Est\Models;

use CA\Crt\Models\Certificate;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstEnrollment extends Model
{
    use HasUuids;
    use Auditable;
    use BelongsToTenant;

    protected $table = 'ca_est_enrollments';

    protected $fillable = [
        'ca_id',
        'tenant_id',
        'type',
        'status',
        'client_identity',
        'csr_pem',
        'certificate_id',
        'key_id',
        'ip_address',
        'user_agent',
    ];

    protected $hidden = [
        'csr_pem',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
            'status' => 'string',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    // ---- Relationships ----

    public function certificateAuthority(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(Key::class, 'key_id');
    }

    // ---- Scopes ----

    public function scopeForCa(Builder $query, string $caId): Builder
    {
        return $query->where('ca_id', $caId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeOlderThan(Builder $query, int $days): Builder
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    // ---- Helpers ----

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markCompleted(string $certificateId, ?string $keyId = null): void
    {
        $this->update([
            'status' => 'completed',
            'certificate_id' => $certificateId,
            'key_id' => $keyId,
        ]);
    }

    public function markFailed(): void
    {
        $this->update([
            'status' => 'failed',
        ]);
    }
}
