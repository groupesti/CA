<?php

declare(strict_types=1);

namespace CA\Acme\Console\Commands;

use CA\Acme\Order\OrderProcessor;
use CA\Acme\Services\NonceManager;
use Illuminate\Console\Command;

class AcmeCleanupCommand extends Command
{
    protected $signature = 'ca:acme:cleanup
        {--dry-run : Show what would be cleaned up without actually doing it}';

    protected $description = 'Remove expired nonces, orders, and authorizations';

    public function handle(OrderProcessor $orderProcessor, NonceManager $nonceManager): int
    {
        $this->info('Starting ACME cleanup...');

        if ($this->option('dry-run')) {
            $this->warn('Dry run mode - no changes will be made.');
        }

        // Count items before cleanup
        $expiredNonces = \CA\Acme\Models\AcmeNonce::where('expires_at', '<', now())
            ->orWhere('used', true)
            ->count();

        $expiredOrders = \CA\Acme\Models\AcmeOrder::where('expires_at', '<', now())
            ->whereIn('status', ['pending', 'ready'])
            ->count();

        $expiredAuthorizations = \CA\Acme\Models\AcmeAuthorization::where('expires_at', '<', now())
            ->where('status', 'pending')
            ->count();

        $this->table(['Resource', 'Count'], [
            ['Expired/Used Nonces', $expiredNonces],
            ['Expired Orders', $expiredOrders],
            ['Expired Authorizations', $expiredAuthorizations],
        ]);

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No changes were made.');

            return self::SUCCESS;
        }

        // Perform cleanup
        $nonceManager->cleanup();
        $results = $orderProcessor->cleanup();

        $this->line('');
        $this->info('Cleanup completed:');
        $this->line("  Nonces removed: {$expiredNonces}");
        $this->line("  Orders expired: {$results['expired_orders']}");
        $this->line("  Authorizations expired: {$results['expired_authorizations']}");

        return self::SUCCESS;
    }
}
