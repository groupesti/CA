<?php

declare(strict_types=1);

namespace CA\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    use HasUuids;

    protected $table = 'ca_certificate_templates';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'certificate_authority_id',
        'name',
        'slug',
        'description',
        'type',
        'key_usage',
        'extended_key_usage',
        'basic_constraints',
        'subject_rules',
        'san_types',
        'allowed_key_types',
        'validity_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
            'key_usage' => 'array',
            'extended_key_usage' => 'array',
            'basic_constraints' => 'array',
            'subject_rules' => 'array',
            'san_types' => 'array',
            'allowed_key_types' => 'array',
            'validity_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function certificateAuthority(): BelongsTo
    {
        return $this->belongsTo(CertificateAuthority::class, 'certificate_authority_id');
    }
}
