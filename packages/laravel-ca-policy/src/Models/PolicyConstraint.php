<?php

declare(strict_types=1);

namespace CA\Policy\Models;

use CA\Models\CertificateAuthority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyConstraint extends Model
{
    protected $table = 'ca_policy_constraints';

    protected $fillable = [
        'policy_id',
        'ca_id',
        'constraint_type',
        'skip_certs',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'skip_certs' => 'integer',
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
}
