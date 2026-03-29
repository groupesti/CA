<?php

declare(strict_types=1);

namespace CA\Oid\Console\Commands;

use CA\Oid\Services\OidRegistry;
use Illuminate\Console\Command;

class OidSearchCommand extends Command
{
    protected $signature = 'ca:oid:search {query : Search term (OID number or name)}';

    protected $description = 'Search OIDs by name or number';

    public function handle(OidRegistry $registry): int
    {
        $query = $this->argument('query');
        $results = $registry->search($query);

        if ($results->isEmpty()) {
            $this->warn("No OIDs found matching \"{$query}\".");

            return self::SUCCESS;
        }

        $this->table(
            ['OID', 'Name', 'Short Name', 'Category', 'Description'],
            $results->map(fn ($info) => [
                $info->oid,
                $info->name,
                $info->shortName ?? '-',
                $info->category,
                str($info->description ?? '')->limit(60),
            ])->toArray(),
        );

        $this->info("Found {$results->count()} result(s).");

        return self::SUCCESS;
    }
}
