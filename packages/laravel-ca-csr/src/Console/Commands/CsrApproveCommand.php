<?php

declare(strict_types=1);

namespace CA\Csr\Console\Commands;

use CA\Csr\Contracts\CsrManagerInterface;
use CA\Exceptions\CsrException;
use Illuminate\Console\Command;

class CsrApproveCommand extends Command
{
    protected $signature = 'ca:csr:approve
        {uuid : The UUID of the CSR to approve}';

    protected $description = 'Approve a pending Certificate Signing Request';

    public function handle(CsrManagerInterface $manager): int
    {
        $uuid = $this->argument('uuid');

        $csr = $manager->findByUuid($uuid);

        if ($csr === null) {
            $this->error("CSR with UUID '{$uuid}' not found.");
            return self::FAILURE;
        }

        $this->info("CSR Details:");
        $this->table(
            ['Field', 'Value'],
            [
                ['UUID', $csr->uuid],
                ['CN', $csr->subject_dn['CN'] ?? 'N/A'],
                ['Status', $csr->status],
                ['Requested By', $csr->requested_by ?? 'N/A'],
            ],
        );

        if (!$this->confirm('Do you want to approve this CSR?')) {
            $this->info('Approval cancelled.');
            return self::SUCCESS;
        }

        try {
            $csr = $manager->approve($csr, 'console');
        } catch (CsrException $e) {
            $this->error("Approval failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info("CSR '{$uuid}' has been approved.");

        return self::SUCCESS;
    }
}
