<?php

declare(strict_types=1);

namespace CA\Scep\Console\Commands;

use CA\Models\CertificateAuthority;
use CA\Scep\Services\ScepChallengeManager;
use Illuminate\Console\Command;

final class ScepChallengeCommand extends Command
{
    protected $signature = 'ca:scep:challenge
        {ca_uuid : The UUID of the Certificate Authority}
        {--purpose= : Purpose description for this challenge password}
        {--ttl= : Time-to-live in seconds (overrides config)}';

    protected $description = 'Generate a SCEP challenge password for device enrollment';

    public function handle(ScepChallengeManager $challengeManager): int
    {
        $caUuid = $this->argument('ca_uuid');
        $ca = CertificateAuthority::find($caUuid);

        if ($ca === null) {
            $this->error('Certificate Authority not found: ' . $caUuid);
            return self::FAILURE;
        }

        $purpose = $this->option('purpose');
        $ttl = $this->option('ttl') !== null ? (int) $this->option('ttl') : null;

        try {
            $password = $challengeManager->generate($ca, $purpose, $ttl);

            $effectiveTtl = $ttl ?? (int) config('ca-scep.challenge_password_ttl', 3600);

            $this->info('Challenge password generated successfully.');
            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['CA', json_encode($ca->subject_dn)],
                    ['Password', $password],
                    ['Purpose', $purpose ?? '(none)'],
                    ['Expires In', $effectiveTtl . ' seconds'],
                    ['Expires At', now()->addSeconds($effectiveTtl)->toIso8601String()],
                ],
            );
            $this->newLine();
            $this->warn('Store this password securely. It cannot be retrieved again.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to generate challenge password: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
