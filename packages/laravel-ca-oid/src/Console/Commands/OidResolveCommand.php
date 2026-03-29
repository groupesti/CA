<?php

declare(strict_types=1);

namespace CA\Oid\Console\Commands;

use CA\Oid\Services\OidRegistry;
use Illuminate\Console\Command;

class OidResolveCommand extends Command
{
    protected $signature = 'ca:oid:resolve {input : OID number or name to resolve}';

    protected $description = 'Resolve an OID to its name, or a name to its OID';

    public function handle(OidRegistry $registry): int
    {
        $input = $this->argument('input');

        // Determine if input looks like an OID (starts with a digit)
        if (preg_match('/^\d/', $input)) {
            $info = $registry->resolve($input);

            if ($info === null) {
                $this->warn("OID not found: {$input}");

                return self::SUCCESS;
            }

            $this->table(
                ['Field', 'Value'],
                [
                    ['OID', $info->oid],
                    ['Name', $info->name],
                    ['Short Name', $info->shortName ?? '-'],
                    ['Description', $info->description ?? '-'],
                    ['Category', $info->category],
                    ['Standard', $info->isStandard ? 'Yes' : 'No'],
                ],
            );
        } else {
            $info = $registry->resolveByName($input);

            if ($info === null) {
                $this->warn("Name not found: {$input}");

                return self::SUCCESS;
            }

            $this->table(
                ['Field', 'Value'],
                [
                    ['OID', $info->oid],
                    ['Name', $info->name],
                    ['Short Name', $info->shortName ?? '-'],
                    ['Description', $info->description ?? '-'],
                    ['Category', $info->category],
                    ['Standard', $info->isStandard ? 'Yes' : 'No'],
                ],
            );
        }

        return self::SUCCESS;
    }
}
