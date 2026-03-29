<?php

declare(strict_types=1);

namespace CA\Traits;

use CA\Models\CertificateAuthority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCertificateAuthority
{
    public function belongsToCa(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'certificate_authority_id');
    }

    public function scopeForCa(Builder $query, string $caId): Builder
    {
        return $query->where('certificate_authority_id', $caId);
    }
}
