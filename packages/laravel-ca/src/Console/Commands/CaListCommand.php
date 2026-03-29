<?php

declare(strict_types=1);

namespace CA\Console\Commands;

use CA\Models\CertificateStatus;
use CA\Models\CertificateAuthority;
use Illuminate\Console\Command;

class CaListCommand extends Command
{
    protected $signature = 'ca:list
        {--tenant= : Filter by tenant ID}
        {--status= : Filter by status (active, revoked, expired, suspended, on_hold)}';

    protected $description = 'List all Certificate Authorities';

    public function handle(): int
    {
        $query = CertificateAuthority::query()
            ->orderBy('created_at');

        if ($tenantId = $this->option('tenant')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($statusValue = $this->option('status')) {
            $status = CertificateStatus::tryFrom($statusValue);

            if ($status === null) {
                $this->error("Invalid status: {$statusValue}");
                $this->line('Valid statuses: ' . implode(', ', array_column(CertificateStatus::cases(), 'slug')));
                return self::FAILURE;
            }

            $query->where('status', $status);
        }

        $cas = $query->get();

        if ($cas->isEmpty()) {
            $this->info('No Certificate Authorities found.');
            return self::SUCCESS;
        }

        $rows = $cas->map(fn(CertificateAuthority $ca): array => [
            $ca->id,
            $ca->subject_dn['CN'] ?? '-',
            $ca->type->slug,
            $ca->status->slug,
            $ca->key_algorithm,
            $ca->isRoot() ? 'Yes' : 'No',
            $ca->tenant_id ?? '-',
            $ca->not_after?->toDateString() ?? '-',
        ])->toArray();

        $this->table(
            ['ID', 'Common Name', 'Type', 'Status', 'Algorithm', 'Root', 'Tenant', 'Expires'],
            $rows,
        );

        $this->newLine();
        $this->line("Total: {$cas->count()} CA(s)");

        return self::SUCCESS;
    }
}
