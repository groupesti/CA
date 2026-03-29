<?php

declare(strict_types=1);

namespace CA\Policy\Console\Commands;

use CA\DTOs\CertificateOptions;
use CA\Models\CertificateType;
use CA\Models\CertificateAuthority;
use CA\Policy\Contracts\PolicyEngineInterface;
use Illuminate\Console\Command;

class PolicyEvaluateCommand extends Command
{
    protected $signature = 'ca:policy:evaluate
        {ca_uuid : The UUID of the Certificate Authority}
        {--type=server_tls : Certificate type}
        {--cn= : Common Name}
        {--san=* : Subject Alternative Names (type:value)}
        {--days=397 : Validity in days}';

    protected $description = 'Test evaluate certificate options against CA policy';

    public function handle(PolicyEngineInterface $engine): int
    {
        $caUuid = $this->argument('ca_uuid');
        $ca = CertificateAuthority::find($caUuid);

        if ($ca === null) {
            $this->error("Certificate Authority '{$caUuid}' not found.");

            return self::FAILURE;
        }

        $typeValue = $this->option('type');
        $type = CertificateType::tryFrom($typeValue);

        if ($type === null) {
            $this->error("Invalid certificate type: {$typeValue}");

            return self::FAILURE;
        }

        $sans = null;
        $sanInputs = $this->option('san');

        if (count($sanInputs) > 0) {
            $sans = [];
            foreach ($sanInputs as $san) {
                $parts = explode(':', $san, 2);
                $sans[] = [
                    'type' => $parts[0] ?? 'dns',
                    'value' => $parts[1] ?? $parts[0],
                ];
            }
        }

        $options = new CertificateOptions(
            type: $type,
            validityDays: (int) $this->option('days'),
            subjectAlternativeNames: $sans,
        );

        $result = $engine->evaluate($ca, $options);

        if ($result->isAllowed()) {
            $this->info('Policy evaluation: ALLOWED');
        } else {
            $this->error('Policy evaluation: DENIED');
        }

        if ($result->action !== null) {
            $this->line("Action: {$result->action}");
        }

        if (count($result->violations) > 0) {
            $this->newLine();
            $this->error('Violations:');
            foreach ($result->violations as $violation) {
                $this->line("  - {$violation}");
            }
        }

        if ($result->hasWarnings()) {
            $this->newLine();
            $this->warn('Warnings:');
            foreach ($result->warnings as $warning) {
                $this->line("  - {$warning}");
            }
        }

        return $result->isAllowed() ? self::SUCCESS : self::FAILURE;
    }
}
