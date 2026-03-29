<?php

declare(strict_types=1);

namespace CA\Crt\Console\Commands;

use CA\Crt\Contracts\CertificateValidatorInterface;
use CA\Crt\Models\Certificate;
use Illuminate\Console\Command;

final class CrtVerifyCommand extends Command
{
    protected $signature = 'ca:crt:verify
        {uuid : Certificate UUID to verify}';

    protected $description = 'Verify a certificate\'s validity';

    public function handle(CertificateValidatorInterface $validator): int
    {
        $certificate = Certificate::query()
            ->where('uuid', $this->argument('uuid'))
            ->first();

        if ($certificate === null) {
            $this->error('Certificate not found: ' . $this->argument('uuid'));
            return self::FAILURE;
        }

        $this->info("Verifying certificate: {$certificate->uuid}");
        $this->info("Subject: " . json_encode($certificate->subject_dn));
        $this->info("Serial: {$certificate->serial_number}");
        $this->newLine();

        $isValid = $validator->validate($certificate);

        // Show individual check results
        $checks = [
            ['Expiry Check', !$certificate->isExpired() ? 'PASS' : 'FAIL'],
            ['Revocation Check', !$certificate->isRevoked() ? 'PASS' : 'FAIL'],
            ['Chain Validation', $validator->validateChain($certificate) ? 'PASS' : 'FAIL'],
        ];

        $this->table(['Check', 'Result'], $checks);

        if ($isValid) {
            $this->info('Certificate is VALID.');
        } else {
            $this->error('Certificate is INVALID.');
            $errors = $validator->getErrors();

            if ($errors !== []) {
                $this->newLine();
                $this->error('Errors:');

                foreach ($errors as $error) {
                    $this->line("  - {$error}");
                }
            }
        }

        return $isValid ? self::SUCCESS : self::FAILURE;
    }
}
