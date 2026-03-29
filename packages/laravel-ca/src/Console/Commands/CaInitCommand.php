<?php

declare(strict_types=1);

namespace CA\Console\Commands;

use CA\DTOs\DistinguishedName;
use CA\Models\KeyAlgorithm;
use CA\Services\CaManager;
use Illuminate\Console\Command;

class CaInitCommand extends Command
{
    protected $signature = 'ca:init
        {--cn= : Common Name}
        {--o= : Organization}
        {--ou= : Organizational Unit}
        {--c= : Country (2-letter code)}
        {--st= : State/Province}
        {--l= : Locality/City}
        {--algorithm= : Key algorithm}
        {--validity= : Validity in days}
        {--path-length= : Path length constraint}
        {--tenant= : Tenant ID}';

    protected $description = 'Create a new Root Certificate Authority interactively';

    public function handle(CaManager $caManager): int
    {
        $this->info('Creating a new Root Certificate Authority');
        $this->newLine();

        $cn = $this->option('cn') ?? $this->ask('Common Name (CN)', 'Root CA');
        $organization = $this->option('o') ?? $this->ask('Organization (O)');
        $ou = $this->option('ou') ?? $this->ask('Organizational Unit (OU)');
        $country = $this->option('c') ?? $this->ask('Country (C, 2-letter code)');
        $state = $this->option('st') ?? $this->ask('State/Province (ST)');
        $locality = $this->option('l') ?? $this->ask('Locality/City (L)');

        $algorithmChoices = array_column(KeyAlgorithm::cases(), 'slug');
        $algorithmValue = $this->option('algorithm') ?? $this->choice(
            'Key Algorithm',
            $algorithmChoices,
            config('ca.defaults.key_algorithm', 'rsa-4096'),
        );

        $algorithm = KeyAlgorithm::from($algorithmValue);

        $defaultValidity = (int) config('ca.defaults.validity.root_ca', 3650);
        $validityDays = (int) ($this->option('validity') ?? $this->ask(
            'Validity (days)',
            (string) $defaultValidity,
        ));

        $pathLengthInput = $this->option('path-length') ?? $this->ask(
            'Path Length Constraint (leave empty for unlimited)',
        );
        $pathLength = $pathLengthInput !== null && $pathLengthInput !== ''
            ? (int) $pathLengthInput
            : null;

        $tenantId = $this->option('tenant');

        $dn = new DistinguishedName(
            commonName: $cn,
            organization: $organization,
            organizationalUnit: $ou,
            country: $country,
            state: $state,
            locality: $locality,
        );

        $this->newLine();
        $this->info('Summary:');
        $this->table(
            ['Field', 'Value'],
            array_filter([
                ['Subject DN', $dn->toString()],
                ['Algorithm', $algorithm->slug . ' (' . $algorithm->getKeySize() . '-bit)'],
                ['Validity', $validityDays . ' days'],
                ['Path Length', $pathLength !== null ? (string) $pathLength : 'Unlimited'],
                ['Tenant', $tenantId ?? 'None'],
            ]),
        );

        if (!$this->confirm('Create this Root CA?', true)) {
            $this->warn('Aborted.');
            return self::FAILURE;
        }

        try {
            $ca = $caManager->createRootCA(
                dn: $dn,
                algorithm: $algorithm,
                validityDays: $validityDays,
                tenantId: $tenantId,
                pathLength: $pathLength,
            );

            $this->newLine();
            $this->info("Root CA created successfully!");
            $this->line("  ID: {$ca->id}");
            $this->line("  Serial: {$ca->serial_number}");
            $this->line("  Valid Until: {$ca->not_after->toDateTimeString()}");
        } catch (\Throwable $e) {
            $this->error("Failed to create Root CA: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
