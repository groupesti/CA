<?php

declare(strict_types=1);

namespace CA\Console\Commands;

use CA\Database\Seeders\CoreLookupSeeder;
use Illuminate\Console\Command;

class SeedLookupsCommand extends Command
{
    protected $signature = 'ca:seed-lookups';

    protected $description = 'Seed all CA lookup tables with default values';

    private const SUB_PACKAGE_SEEDERS = [
        \CA\Key\Database\Seeders\KeyLookupSeeder::class,
        \CA\Csr\Database\Seeders\CsrLookupSeeder::class,
        \CA\Acme\Database\Seeders\AcmeLookupSeeder::class,
        \CA\Scep\Database\Seeders\ScepLookupSeeder::class,
        \CA\Policy\Database\Seeders\PolicyLookupSeeder::class,
    ];

    public function handle(): int
    {
        $this->info('Seeding CA lookup tables...');

        $this->components->task('Core lookups', function () {
            $seeder = new CoreLookupSeeder();
            $seeder->run();
        });

        foreach (self::SUB_PACKAGE_SEEDERS as $seederClass) {
            $shortName = class_basename($seederClass);

            if (!class_exists($seederClass)) {
                $this->components->warn("Skipping {$shortName} (package not installed)");

                continue;
            }

            $this->components->task($shortName, function () use ($seederClass) {
                $seeder = new $seederClass();
                $seeder->run();
            });
        }

        $this->newLine();
        $this->info('All CA lookup tables seeded successfully.');

        return self::SUCCESS;
    }
}
