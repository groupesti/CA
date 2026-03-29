<?php

declare(strict_types=1);

namespace CA\OCSP\Models;

use CA\Models\CertificateAuthority;
use CA\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcspResponse extends Model
{
    use HasUuids;
    use BelongsToTenant;

    protected $table = 'ca_ocsp_responses';

    protected $fillable = [
        'ca_id',
        'tenant_id',
        'certificate_serial',
        'status',
        'this_update',
        'next_update',
        'response_der',
        'revocation_time',
        'revocation_reason',
    ];

    protected function casts(): array
    {
        return [
            'this_update' => 'datetime',
            'next_update' => 'datetime',
            'revocation_time' => 'datetime',
            'revocation_reason' => 'integer',
        ];
    }

    // ---- Relationships ----

    public function certificateAuthority(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'ca_id');
    }
}
