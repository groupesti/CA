<?php

declare(strict_types=1);

namespace CA\Oid\Console\Commands;

use CA\Oid\Database\Seeders\StandardOidSeeder;
use Illuminate\Console\Command;

class OidSeedCommand extends Command
{
    protected $signature = 'ca:oid:seed';

    protected $description = 'Seed the database with all standard OIDs';

    public function handle(): int
    {
        $this->info('Seeding standard OIDs...');

        $seeder = new StandardOidSeeder();
        $seeder->run();

        $this->info('Standard OIDs seeded successfully.');

        return self::SUCCESS;
    }
}
