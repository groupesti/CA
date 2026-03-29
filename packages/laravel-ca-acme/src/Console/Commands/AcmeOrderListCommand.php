<?php

declare(strict_types=1);

namespace CA\Acme\Console\Commands;

use CA\Models\OrderStatus;
use CA\Acme\Models\AcmeOrder;
use Illuminate\Console\Command;

class AcmeOrderListCommand extends Command
{
    protected $signature = 'ca:acme:orders
        {--status= : Filter by status (pending, ready, processing, valid, invalid, expired, revoked)}
        {--account= : Filter by account ID}
        {--limit=50 : Maximum number of orders to display}';

    protected $description = 'List ACME orders with optional status filter';

    public function handle(): int
    {
        $query = AcmeOrder::query()->with('account');

        if ($status = $this->option('status')) {
            $validStatuses = [
                OrderStatus::PENDING,
                OrderStatus::READY,
                OrderStatus::PROCESSING,
                OrderStatus::VALID,
                OrderStatus::INVALID,
            ];

            if (!in_array($status, $validStatuses, true)) {
                $this->error("Invalid status: {$status}. Valid values: " . implode(', ', $validStatuses));

                return self::FAILURE;
            }

            $query->where('status', $status);
        }

        if ($accountId = $this->option('account')) {
            $query->where('account_id', $accountId);
        }

        $orders = $query
            ->orderByDesc('created_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No ACME orders found.');

            return self::SUCCESS;
        }

        $rows = $orders->map(fn(AcmeOrder $order): array => [
            $order->id,
            $order->status,
            $order->account_id,
            json_encode(collect($order->identifiers)->pluck('value')->toArray()),
            $order->certificate_id ?? '-',
            $order->expires_at?->toDateTimeString(),
            $order->created_at?->toDateTimeString(),
        ])->toArray();

        $this->table(
            ['ID', 'Status', 'Account', 'Identifiers', 'Certificate', 'Expires', 'Created'],
            $rows,
        );

        $this->line('');
        $this->info("Total: {$orders->count()} order(s)");

        return self::SUCCESS;
    }
}
