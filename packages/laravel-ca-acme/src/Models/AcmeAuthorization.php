<?php

declare(strict_types=1);

namespace CA\Acme\Models;

use CA\Models\AuthorizationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcmeAuthorization extends Model
{
    use HasUuids;

    protected $table = 'ca_acme_authorizations';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'identifier_type',
        'identifier_value',
        'status',
        'wildcard',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'wildcard' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    // ---- Relationships ----

    public function order(): BelongsTo
    {
        return $this->belongsTo(AcmeOrder::class, 'order_id');
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(AcmeChallenge::class, 'authorization_id');
    }
}
