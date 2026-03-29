<?php

declare(strict_types=1);

namespace CA\Tsa\Console\Commands;

use CA\Tsa\Contracts\TsaVerifierInterface;
use Illuminate\Console\Command;

/**
 * Verify a timestamp response file.
 */
class TsaVerifyCommand extends Command
{
    protected $signature = 'ca:tsa:verify
        {file : Path to the TSR (timestamp response) DER file}
        {--data= : Optional path to the original data file for imprint verification}';

    protected $description = 'Verify a timestamp response file';

    public function handle(TsaVerifierInterface $verifier): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $tsrDer = file_get_contents($filePath);
        if ($tsrDer === false || $tsrDer === '') {
            $this->error("Could not read file: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Verifying timestamp response: {$filePath}");

        // Extract TSTInfo first
        try {
            $tstInfo = $verifier->extractTstInfo($tsrDer);

            $this->line('');
            $this->info('TSTInfo:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Version', (string) $tstInfo['version']],
                    ['Policy', $tstInfo['policy']],
                    ['Hash Algorithm', $tstInfo['hashAlgorithm']],
                    ['Message Imprint', $tstInfo['hashedMessage']],
                    ['Serial Number', $tstInfo['serialNumber']],
                    ['Generation Time', $tstInfo['genTime']],
                    ['Ordering', $tstInfo['ordering'] ? 'Yes' : 'No'],
                    ['Nonce', $tstInfo['nonce'] ?? 'None'],
                ],
            );

            if ($tstInfo['accuracy'] !== null) {
                $this->info('Accuracy:');
                $this->table(
                    ['Component', 'Value'],
                    [
                        ['Seconds', (string) ($tstInfo['accuracy']['seconds'] ?? 0)],
                        ['Milliseconds', (string) ($tstInfo['accuracy']['millis'] ?? 0)],
                        ['Microseconds', (string) ($tstInfo['accuracy']['micros'] ?? 0)],
                    ],
                );
            }
        } catch (\Throwable $e) {
            $this->error("Failed to extract TSTInfo: {$e->getMessage()}");

            return self::FAILURE;
        }

        // Verify
        $originalData = null;
        $dataPath = $this->option('data');
        if ($dataPath !== null) {
            if (!file_exists($dataPath)) {
                $this->error("Data file not found: {$dataPath}");

                return self::FAILURE;
            }

            $originalData = file_get_contents($dataPath);
            if ($originalData === false) {
                $this->error("Could not read data file: {$dataPath}");

                return self::FAILURE;
            }
        }

        $valid = $verifier->verify($tsrDer, $originalData);

        $this->line('');
        if ($valid) {
            $this->info('Verification: PASSED');
        } else {
            $this->error('Verification: FAILED');
        }

        return $valid ? self::SUCCESS : self::FAILURE;
    }
}
