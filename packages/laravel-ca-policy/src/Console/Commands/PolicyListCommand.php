<?php

declare(strict_types=1);

namespace CA\Policy\Console\Commands;

use CA\Policy\Models\CertificatePolicy;
use Illuminate\Console\Command;

class PolicyListCommand extends Command
{
    protected $signature = 'ca:policy:list {--ca= : Filter by Certificate Authority UUID}';

    protected $description = 'List certificate policies';

    public function handle(): int
    {
        $query = CertificatePolicy::query();

        $caId = $this->option('ca');

        if ($caId !== null) {
            $query->where('ca_id', $caId);
        }

        $policies = $query->get();

        if ($policies->isEmpty()) {
            $this->info('No policies found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'CA ID', 'Name', 'Policy OID', 'CPS URI', 'Default', 'Enabled'],
            $policies->map(fn (CertificatePolicy $p): array => [
                $p->id,
                $p->ca_id,
                $p->name,
                $p->policy_oid,
                $p->cps_uri ?? '-',
                $p->is_default ? 'Yes' : 'No',
                $p->enabled ? 'Yes' : 'No',
            ])->toArray(),
        );

        return self::SUCCESS;
    }
}
