<?php

declare(strict_types=1);

namespace CA\Scep\Models;

use CA\Crt\Models\Certificate;
use CA\Models\CertificateAuthority;
use CA\Models\ScepPkiStatus;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScepTransaction extends Model
{
    use HasUuids;
    use BelongsToTenant;
    use Auditable;

    protected $table = 'ca_scep_transactions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'ca_id',
        'tenant_id',
        'transaction_id',
        'message_type',
        'status',
        'sender_nonce',
        'recipient_nonce',
        'csr_pem',
        'certificate_id',
        'challenge_password',
        'device_identifier',
        'error_info',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'message_type' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    // ---- Relationships ----

    public function ca(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }

    // ---- Scopes ----

    public function scopeForCa(Builder $query, string $caId): Builder
    {
        return $query->where('ca_id', $caId);
    }

    public function scopeByStatus(Builder $query, ScepPkiStatus $status): Builder
    {
        return $query->where('status', $status->slug);
    }

    public function scopeByTransactionId(Builder $query, string $transactionId): Builder
    {
        return $query->where('transaction_id', $transactionId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ScepPkiStatus::PENDING);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '>=', now());
    }

    // ---- Helpers ----

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === ScepPkiStatus::PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->status === ScepPkiStatus::SUCCESS;
    }
}
