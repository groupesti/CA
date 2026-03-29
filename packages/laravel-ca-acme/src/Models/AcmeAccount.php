<?php

declare(strict_types=1);

namespace CA\Acme\Models;

use CA\Models\CertificateAuthority;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcmeAccount extends Model
{
    use HasUuids;
    use Auditable;
    use BelongsToTenant;

    /** ACME account status constants per RFC 8555 */
    public const STATUS_VALID = 'valid';
    public const STATUS_DEACTIVATED = 'deactivated';
    public const STATUS_REVOKED = 'revoked';

    protected $table = 'ca_acme_accounts';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'ca_id',
        'tenant_id',
        'status',
        'contact',
        'public_key_jwk',
        'account_key_thumbprint',
        'terms_agreed',
        'external_account_id',
    ];

    protected function casts(): array
    {
        return [
            'contact' => 'array',
            'public_key_jwk' => 'array',
            'terms_agreed' => 'boolean',
        ];
    }

    // ---- Status helpers ----

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    public function isDeactivated(): bool
    {
        return $this->status === self::STATUS_DEACTIVATED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    // ---- Relationships ----

    public function certificateAuthority(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(AcmeOrder::class, 'account_id');
    }
}
