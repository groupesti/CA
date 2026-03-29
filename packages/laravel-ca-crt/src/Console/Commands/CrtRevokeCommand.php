<?php

declare(strict_types=1);

namespace CA\Crt\Console\Commands;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Models\Certificate;
use CA\Models\RevocationReason;
use Illuminate\Console\Command;

final class CrtRevokeCommand extends Command
{
    protected $signature = 'ca:crt:revoke
        {uuid : Certificate UUID to revoke}
        {--reason= : Revocation reason code (0-10)}';

    protected $description = 'Revoke a certificate';

    public function handle(CertificateManagerInterface $manager): int
    {
        $certificate = Certificate::query()
            ->where('uuid', $this->argument('uuid'))
            ->first();

        if ($certificate === null) {
            $this->error('Certificate not found: ' . $this->argument('uuid'));
            return self::FAILURE;
        }

        if ($certificate->isRevoked()) {
            $this->error('Certificate is already revoked.');
            return self::FAILURE;
        }

        $reasonCode = $this->option('reason') ?? $this->choice(
            'Revocation reason',
            array_map(
                fn(RevocationReason $r) => $r->slug . ' - ' . $r->name,
                RevocationReason::cases(),
            ),
            '0 - Unspecified',
        );

        // Extract numeric code if full label was chosen
        $code = (int) explode(' ', (string) $reasonCode)[0];
        $reason = RevocationReason::from($code);

        $this->info("Revoking certificate: {$certificate->uuid}");
        $this->info("Subject: " . json_encode($certificate->subject_dn));
        $this->info("Reason: {$reason->name}");

        if (!$this->confirm('Are you sure you want to revoke this certificate?')) {
            $this->info('Revocation cancelled.');
            return self::SUCCESS;
        }

        try {
            $manager->revoke($certificate, $reason);
            $this->info('Certificate revoked successfully.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to revoke certificate: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
