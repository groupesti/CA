<?php

declare(strict_types=1);

namespace CA\Crt\Console\Commands;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Events\CertificateExpiring;
use CA\Crt\Models\Certificate;
use Illuminate\Console\Command;

final class CrtExpiringScanCommand extends Command
{
    protected $signature = 'ca:crt:expiring
        {--days=30 : Number of days to look ahead}
        {--notify : Fire CertificateExpiring events for each certificate}';

    protected $description = 'Scan for certificates expiring soon';

    public function handle(CertificateManagerInterface $manager): int
    {
        $days = (int) $this->option('days');

        $expiring = $manager->getExpiring($days);

        if ($expiring->isEmpty()) {
            $this->info("No certificates expiring within {$days} days.");
            return self::SUCCESS;
        }

        $this->table(
            ['UUID', 'Serial', 'CN', 'Expires', 'Days Left'],
            $expiring->map(fn(Certificate $cert) => [
                $cert->uuid,
                substr($cert->serial_number, 0, 16) . '...',
                $cert->subject_dn['CN'] ?? '-',
                $cert->not_after?->toDateString() ?? '-',
                $cert->daysUntilExpiry(),
            ])->toArray(),
        );

        $this->info("{$expiring->count()} certificate(s) expiring within {$days} days.");

        if ($this->option('notify')) {
            foreach ($expiring as $cert) {
                event(new CertificateExpiring($cert, $cert->daysUntilExpiry()));
            }

            $this->info('CertificateExpiring events fired for all expiring certificates.');
        }

        return self::SUCCESS;
    }
}
