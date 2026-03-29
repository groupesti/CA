<?php

declare(strict_types=1);

namespace CA\Scep\Console\Commands;

use CA\Scep\Models\ScepChallengePassword;
use CA\Scep\Models\ScepTransaction;
use CA\Scep\Services\ScepChallengeManager;
use Illuminate\Console\Command;

final class ScepCleanupCommand extends Command
{
    protected $signature = 'ca:scep:cleanup
        {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Remove expired SCEP transactions and challenge passwords';

    public function handle(ScepChallengeManager $challengeManager): int
    {
        $dryRun = $this->option('dry-run');

        // Count expired transactions
        $expiredTransactions = ScepTransaction::query()
            ->where('expires_at', '<', now())
            ->count();

        // Count expired challenge passwords
        $expiredChallenges = ScepChallengePassword::query()
            ->where('expires_at', '<', now())
            ->count();

        if ($dryRun) {
            $this->info('Dry run - no records will be deleted.');
            $this->newLine();
        }

        $this->info("Expired SCEP transactions: {$expiredTransactions}");
        $this->info("Expired challenge passwords: {$expiredChallenges}");

        if ($expiredTransactions === 0 && $expiredChallenges === 0) {
            $this->info('Nothing to clean up.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            return self::SUCCESS;
        }

        // Delete expired transactions
        $deletedTransactions = ScepTransaction::query()
            ->where('expires_at', '<', now())
            ->delete();

        // Delete expired challenge passwords
        $deletedChallenges = $challengeManager->cleanupExpired();

        $this->newLine();
        $this->info("Deleted {$deletedTransactions} expired transactions.");
        $this->info("Deleted {$deletedChallenges} expired challenge passwords.");

        return self::SUCCESS;
    }
}
