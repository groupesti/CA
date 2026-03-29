<?php

declare(strict_types=1);

namespace CA\Acme\Models;

use CA\Models\ChallengeStatus;
use CA\Models\ChallengeType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcmeChallenge extends Model
{
    use HasUuids;

    protected $table = 'ca_acme_challenges';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'authorization_id',
        'type',
        'status',
        'token',
        'key_authorization',
        'validated_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',
            'status' => 'string',
            'validated_at' => 'datetime',
            'error' => 'array',
        ];
    }

    // ---- Relationships ----

    public function authorization(): BelongsTo
    {
        return $this->belongsTo(AcmeAuthorization::class, 'authorization_id');
    }
}
