<?php

declare(strict_types=1);

namespace CA\Acme\Models;

use CA\Models\OrderStatus;
use CA\Crt\Models\Certificate;
use CA\Models\CertificateAuthority;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcmeOrder extends Model
{
    use HasUuids;
    use Auditable;
    use BelongsToTenant;

    protected $table = 'ca_acme_orders';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'ca_id',
        'tenant_id',
        'status',
        'identifiers',
        'not_before',
        'not_after',
        'certificate_id',
        'finalize_csr',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'identifiers' => 'array',
            'not_before' => 'datetime',
            'not_after' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // ---- Relationships ----

    public function account(): BelongsTo
    {
        return $this->belongsTo(AcmeAccount::class, 'account_id');
    }

    public function certificateAuthority(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function authorizations(): HasMany
    {
        return $this->hasMany(AcmeAuthorization::class, 'order_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }
}
