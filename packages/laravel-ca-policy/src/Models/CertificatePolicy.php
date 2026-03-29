<?php

declare(strict_types=1);

namespace CA\Policy\Models;

use CA\Models\CertificateAuthority;
use CA\Traits\Auditable;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificatePolicy extends Model
{
    use HasUuids;
    use Auditable;
    use BelongsToTenant;

    protected $table = 'ca_certificate_policies';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'ca_id',
        'tenant_id',
        'name',
        'policy_oid',
        'cps_uri',
        'description',
        'is_default',
        'enabled',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }

    // ---- Relationships ----

    public function ca(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }

    public function constraints(): HasMany
    {
        return $this->hasMany(NameConstraint::class, 'policy_id');
    }

    public function policyConstraints(): HasMany
    {
        return $this->hasMany(PolicyConstraint::class, 'policy_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(IssuanceRule::class, 'policy_id');
    }
}
