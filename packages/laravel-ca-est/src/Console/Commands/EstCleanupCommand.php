<?php

declare(strict_types=1);

namespace CA\Est\Console\Commands;

use CA\Est\Models\EstEnrollment;
use Illuminate\Console\Command;

class EstCleanupCommand extends Command
{
    protected $signature = 'ca:est:cleanup
        {--days= : Number of days to retain records (default: from config)}
        {--status= : Only clean up records with this status}
        {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Remove old EST enrollment records';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('ca-est.enrollment_retention_days', 90));
        $dryRun = (bool) $this->option('dry-run');

        $query = EstEnrollment::query()->olderThan($days);

        if ($status = $this->option('status')) {
            $query->byStatus($status);
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No enrollment records to clean up.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Would delete {$count} enrollment record(s) older than {$days} days.");

            return self::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} enrollment record(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
