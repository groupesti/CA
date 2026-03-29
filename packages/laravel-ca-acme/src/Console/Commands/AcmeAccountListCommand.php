<?php

declare(strict_types=1);

namespace CA\Acme\Console\Commands;

use CA\Acme\Models\AcmeAccount;
use Illuminate\Console\Command;

class AcmeAccountListCommand extends Command
{
    protected $signature = 'ca:acme:accounts
        {--status= : Filter by status (valid, deactivated, revoked)}
        {--ca= : Filter by CA ID}
        {--limit=50 : Maximum number of accounts to display}';

    protected $description = 'List ACME accounts';

    public function handle(): int
    {
        $query = AcmeAccount::query()->with('certificateAuthority');

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        if ($caId = $this->option('ca')) {
            $query->where('ca_id', $caId);
        }

        $accounts = $query
            ->orderByDesc('created_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('No ACME accounts found.');

            return self::SUCCESS;
        }

        $rows = $accounts->map(fn(AcmeAccount $account): array => [
            $account->id,
            $account->status,
            implode(', ', $account->contact ?? []),
            $account->ca_id,
            $account->terms_agreed ? 'Yes' : 'No',
            $account->orders()->count(),
            $account->created_at?->toDateTimeString(),
        ])->toArray();

        $this->table(
            ['ID', 'Status', 'Contact', 'CA ID', 'ToS Agreed', 'Orders', 'Created'],
            $rows,
        );

        $this->line('');
        $this->info("Total: {$accounts->count()} account(s)");

        return self::SUCCESS;
    }
}
