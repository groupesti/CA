<?php

declare(strict_types=1);

namespace CA\Crt\Console\Commands;

use CA\Crt\Models\Certificate;
use Illuminate\Console\Command;

final class CrtListCommand extends Command
{
    protected $signature = 'ca:crt:list
        {--ca= : Filter by Certificate Authority ID}
        {--type= : Filter by certificate type}
        {--status= : Filter by status (active, revoked, expired)}
        {--tenant= : Filter by tenant ID}';

    protected $description = 'List certificates';

    public function handle(): int
    {
        $query = Certificate::query();

        if ($this->option('ca')) {
            $query->where('ca_id', $this->option('ca'));
        }

        if ($this->option('type')) {
            $query->where('type', $this->option('type'));
        }

        if ($this->option('status')) {
            $query->where('status', $this->option('status'));
        }

        if ($this->option('tenant')) {
            $query->where('tenant_id', $this->option('tenant'));
        }

        $certificates = $query->orderByDesc('created_at')->get();

        if ($certificates->isEmpty()) {
            $this->info('No certificates found.');
            return self::SUCCESS;
        }

        $this->table(
            ['UUID', 'Serial', 'Type', 'CN', 'Status', 'Not After', 'Days Left'],
            $certificates->map(fn(Certificate $cert) => [
                $cert->uuid,
                substr($cert->serial_number, 0, 16) . '...',
                $cert->type->slug,
                $cert->subject_dn['CN'] ?? '-',
                $cert->status->slug,
                $cert->not_after?->toDateString() ?? '-',
                $cert->daysUntilExpiry(),
            ])->toArray(),
        );

        $this->info("Total: {$certificates->count()} certificate(s).");

        return self::SUCCESS;
    }
}
