<?php

declare(strict_types=1);

namespace CA\Crt\Console\Commands;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Models\Certificate;
use Illuminate\Console\Command;

final class CrtRenewCommand extends Command
{
    protected $signature = 'ca:crt:renew
        {uuid : Certificate UUID to renew}
        {--days= : New validity period in days}';

    protected $description = 'Renew a certificate';

    public function handle(CertificateManagerInterface $manager): int
    {
        $certificate = Certificate::query()
            ->where('uuid', $this->argument('uuid'))
            ->first();

        if ($certificate === null) {
            $this->error('Certificate not found: ' . $this->argument('uuid'));
            return self::FAILURE;
        }

        $days = $this->option('days') ? (int) $this->option('days') : null;

        try {
            $newCertificate = $manager->renew($certificate, $days);

            $this->info('Certificate renewed successfully.');
            $this->table(
                ['Field', 'Value'],
                [
                    ['New UUID', $newCertificate->uuid],
                    ['New Serial', $newCertificate->serial_number],
                    ['Fingerprint', $newCertificate->fingerprint_sha256],
                    ['Not Before', $newCertificate->not_before->toIso8601String()],
                    ['Not After', $newCertificate->not_after->toIso8601String()],
                    ['Previous UUID', $certificate->uuid],
                ],
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to renew certificate: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
