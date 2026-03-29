<?php

declare(strict_types=1);

namespace CA\Csr\Console\Commands;

use CA\Models\CsrStatus;
use CA\Csr\Models\Csr;
use Illuminate\Console\Command;

class CsrListCommand extends Command
{
    protected $signature = 'ca:csr:list
        {--ca= : Filter by Certificate Authority ID}
        {--status= : Filter by status (pending, approved, rejected, signed, expired)}
        {--tenant= : Filter by tenant ID}';

    protected $description = 'List Certificate Signing Requests';

    public function handle(): int
    {
        $query = Csr::query();

        if ($caId = $this->option('ca')) {
            $query->where('ca_id', $caId);
        }

        if ($status = $this->option('status')) {
            $validStatuses = [CsrStatus::PENDING, CsrStatus::APPROVED, CsrStatus::REJECTED, CsrStatus::SIGNED];
            if (!in_array($status, $validStatuses, true)) {
                $this->error("Invalid status '{$status}'. Valid: pending, approved, rejected, signed.");
                return self::FAILURE;
            }
            $query->where('status', $status);
        }

        if ($tenantId = $this->option('tenant')) {
            $query->where('tenant_id', $tenantId);
        }

        $csrs = $query->latest()->get();

        if ($csrs->isEmpty()) {
            $this->info('No CSRs found.');
            return self::SUCCESS;
        }

        $rows = $csrs->map(fn (Csr $csr): array => [
            $csr->uuid,
            $csr->subject_dn['CN'] ?? 'N/A',
            $csr->status,
            $csr->ca_id ?? 'N/A',
            $csr->requested_by ?? 'N/A',
            $csr->expires_at?->toDateTimeString() ?? 'N/A',
            $csr->created_at?->toDateTimeString() ?? 'N/A',
        ])->toArray();

        $this->table(
            ['UUID', 'CN', 'Status', 'CA ID', 'Requested By', 'Expires', 'Created'],
            $rows,
        );

        return self::SUCCESS;
    }
}
