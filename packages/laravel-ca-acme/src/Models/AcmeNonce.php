<?php

declare(strict_types=1);

namespace CA\Acme\Models;

use Illuminate\Database\Eloquent\Model;

class AcmeNonce extends Model
{
    protected $table = 'ca_acme_nonces';

    public $timestamps = false;

    protected $fillable = [
        'nonce',
        'expires_at',
        'used',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used' => 'boolean',
        ];
    }
}
