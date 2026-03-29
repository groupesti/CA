<?php

declare(strict_types=1);

namespace CA\Key\Console\Commands;

use CA\Key\Models\Key;
use Illuminate\Console\Command;

class KeyListCommand extends Command
{
    protected $signature = 'ca:key:list
        {--ca= : Filter by Certificate Authority ID}
        {--tenant= : Filter by tenant ID}
        {--algorithm= : Filter by algorithm}
        {--status=active : Filter by status}';

    protected $description = 'List all cryptographic keys';

    public function handle(): int
    {
        $query = Key::query();

        if ($this->option('ca') !== null) {
            $query->where('ca_id', $this->option('ca'));
        }

        if ($this->option('tenant') !== null) {
            $query->where('tenant_id', $this->option('tenant'));
        }

        if ($this->option('algorithm') !== null) {
            $query->where('algorithm', $this->option('algorithm'));
        }

        if ($this->option('status') !== null) {
            $query->where('status', $this->option('status'));
        }

        $keys = $query->latest()->get();

        if ($keys->isEmpty()) {
            $this->info('No keys found.');

            return self::SUCCESS;
        }

        $this->table(
            ['UUID', 'Algorithm', 'Fingerprint', 'Status', 'Usage', 'Created'],
            $keys->map(fn (Key $key): array => [
                $key->uuid,
                $key->algorithm->slug,
                $key->fingerprint_sha256,
                $key->status->slug,
                $key->usage,
                $key->created_at?->toDateTimeString(),
            ])->toArray(),
        );

        $this->info("Total: {$keys->count()} key(s).");

        return self::SUCCESS;
    }
}
