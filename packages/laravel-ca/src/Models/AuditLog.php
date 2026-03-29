<?php

declare(strict_types=1);

namespace CA\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasUuids;

    protected $table = 'ca_audit_logs';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'action',
        'subject_type',
        'subject_id',
        'actor_type',
        'actor_id',
        'metadata',
        'ip_address',
        'user_agent',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    public function actor(): MorphTo
    {
        return $this->morphTo('actor');
    }
}
