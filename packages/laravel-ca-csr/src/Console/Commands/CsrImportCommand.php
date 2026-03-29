<?php

declare(strict_types=1);

namespace CA\Csr\Console\Commands;

use CA\Csr\Contracts\CsrManagerInterface;
use CA\Exceptions\CsrException;
use Illuminate\Console\Command;

class CsrImportCommand extends Command
{
    protected $signature = 'ca:csr:import
        {file : Path to the PEM-encoded CSR file}
        {--ca= : Certificate Authority ID (required)}';

    protected $description = 'Import a CSR from a PEM file';

    public function handle(CsrManagerInterface $manager): int
    {
        $filePath = $this->argument('file');
        $caId = $this->option('ca');

        if ($caId === null) {
            $this->error('The --ca option is required.');
            return self::FAILURE;
        }

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $pem = file_get_contents($filePath);

        if ($pem === false) {
            $this->error("Unable to read file: {$filePath}");
            return self::FAILURE;
        }

        try {
            $csr = $manager->import($pem);
            $csr->update(['ca_id' => $caId]);
            $csr->refresh();
        } catch (CsrException $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info('CSR imported successfully.');
        $this->table(
            ['Field', 'Value'],
            [
                ['UUID', $csr->uuid],
                ['CN', $csr->subject_dn['CN'] ?? 'N/A'],
                ['Status', $csr->status],
                ['CA ID', $csr->ca_id ?? 'N/A'],
            ],
        );

        return self::SUCCESS;
    }
}
