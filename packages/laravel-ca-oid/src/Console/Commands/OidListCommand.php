<?php

declare(strict_types=1);

namespace CA\Oid\Console\Commands;

use CA\Oid\Services\OidRegistry;
use Illuminate\Console\Command;

class OidListCommand extends Command
{
    protected $signature = 'ca:oid:list
        {--category= : Filter by category}';

    protected $description = 'List all registered OIDs';

    public function handle(OidRegistry $registry): int
    {
        $category = $this->option('category');

        if ($category !== null) {
            $results = $registry->getByCategory($category);
            $this->info("OIDs in category: {$category}");
        } else {
            // Show all categories and their counts
            $categories = $registry->getCategories();

            if (empty($categories)) {
                $this->warn('No OIDs found.');

                return self::SUCCESS;
            }

            $results = collect();
            foreach ($categories as $cat) {
                $results = $results->merge($registry->getByCategory($cat));
            }
        }

        if ($results->isEmpty()) {
            $this->warn('No OIDs found.');

            return self::SUCCESS;
        }

        $this->table(
            ['OID', 'Name', 'Short Name', 'Category', 'Standard'],
            $results->map(fn ($info) => [
                $info->oid,
                $info->name,
                $info->shortName ?? '-',
                $info->category,
                $info->isStandard ? 'Yes' : 'No',
            ])->toArray(),
        );

        $this->info("Total: {$results->count()} OID(s).");

        return self::SUCCESS;
    }
}
