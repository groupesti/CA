<?php

declare(strict_types=1);

namespace CA\Scep\Console\Commands;

use CA\Models\ScepPkiStatus;
use CA\Scep\Models\ScepTransaction;
use Illuminate\Console\Command;

final class ScepTransactionListCommand extends Command
{
    protected $signature = 'ca:scep:transactions
        {--ca= : Filter by Certificate Authority UUID}
        {--status= : Filter by status (0=SUCCESS, 2=FAILURE, 3=PENDING)}
        {--limit=25 : Number of records to display}';

    protected $description = 'List SCEP enrollment transactions';

    public function handle(): int
    {
        $query = ScepTransaction::query()->with(['ca', 'certificate']);

        if ($this->option('ca')) {
            $query->forCa($this->option('ca'));
        }

        if ($this->option('status') !== null) {
            $status = ScepPkiStatus::fromSlug($this->option('status'));
            $query->byStatus($status);
        }

        $limit = (int) $this->option('limit');
        $transactions = $query->orderByDesc('created_at')->limit($limit)->get();

        if ($transactions->isEmpty()) {
            $this->info('No SCEP transactions found.');
            return self::SUCCESS;
        }

        $rows = $transactions->map(function (ScepTransaction $tx) {
            return [
                $tx->id,
                $tx->transaction_id,
                $tx->ca?->id ?? 'N/A',
                $tx->message_type,
                match ($tx->status) {
                    ScepPkiStatus::SUCCESS => '<fg=green>SUCCESS</>',
                    ScepPkiStatus::FAILURE => '<fg=red>FAILURE</>',
                    ScepPkiStatus::PENDING => '<fg=yellow>PENDING</>',
                    default => (string) $tx->status,
                },
                $tx->certificate_id ?? '-',
                $tx->device_identifier ?? '-',
                $tx->expires_at?->toIso8601String() ?? '-',
                $tx->created_at?->toIso8601String() ?? '-',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Transaction ID', 'CA ID', 'Type', 'Status', 'Cert ID', 'Device', 'Expires', 'Created'],
            $rows,
        );

        $this->info("Showing {$transactions->count()} of {$limit} max records.");

        return self::SUCCESS;
    }
}
