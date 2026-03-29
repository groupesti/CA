<?php

declare(strict_types=1);

namespace CA\Crt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateChain extends Model
{
    protected $table = 'ca_certificate_chains';

    protected $fillable = [
        'certificate_id',
        'parent_certificate_id',
        'depth',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
        ];
    }

    // ---- Relationships ----

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }

    public function parentCertificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'parent_certificate_id');
    }
}
