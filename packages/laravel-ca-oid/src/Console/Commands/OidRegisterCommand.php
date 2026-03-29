<?php

declare(strict_types=1);

namespace CA\Oid\Console\Commands;

use CA\Oid\Services\OidRegistry;
use Illuminate\Console\Command;

class OidRegisterCommand extends Command
{
    protected $signature = 'ca:oid:register
        {oid : The OID to register (e.g. 1.2.3.4.5)}
        {name : Human-readable name for the OID}
        {--description= : Description of the OID}
        {--category= : Category (e.g. custom, eku, x509)}';

    protected $description = 'Register a custom OID';

    public function handle(OidRegistry $registry): int
    {
        $oid = $this->argument('oid');
        $name = $this->argument('name');

        if (! $registry->validate($oid)) {
            $this->error("Invalid OID format: {$oid}");

            return self::FAILURE;
        }

        if ($registry->exists($oid)) {
            $this->error("OID {$oid} is already registered.");

            return self::FAILURE;
        }

        try {
            $entry = $registry->register(
                oid: $oid,
                name: $name,
                description: $this->option('description') ?? '',
                category: $this->option('category'),
            );

            $this->info("OID registered successfully: {$entry->oid} ({$entry->name})");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to register OID: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
