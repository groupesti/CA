<?php

declare(strict_types=1);

namespace CA\Policy\Console\Commands;

use CA\Policy\Models\IssuanceRule;
use Illuminate\Console\Command;

class IssuanceRuleListCommand extends Command
{
    protected $signature = 'ca:policy:rules {--ca= : Filter by Certificate Authority UUID}';

    protected $description = 'List issuance rules';

    public function handle(): int
    {
        $query = IssuanceRule::query()->byPriority();

        $caId = $this->option('ca');

        if ($caId !== null) {
            $query->where('ca_id', $caId);
        }

        $rules = $query->get();

        if ($rules->isEmpty()) {
            $this->info('No issuance rules found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'CA ID', 'Name', 'Rule Class', 'Priority', 'Severity', 'Action', 'Enabled'],
            $rules->map(fn (IssuanceRule $r): array => [
                $r->id,
                $r->ca_id,
                $r->name,
                class_basename($r->rule_class),
                $r->priority,
                $r->severity,
                $r->action_on_failure,
                $r->enabled ? 'Yes' : 'No',
            ])->toArray(),
        );

        return self::SUCCESS;
    }
}
