<?php

declare(strict_types=1);

namespace CA\Key\Console\Commands;

use CA\Models\KeyAlgorithm;
use CA\Key\Contracts\KeyManagerInterface;
use Illuminate\Console\Command;

class KeyGenerateCommand extends Command
{
    protected $signature = 'ca:key:generate
        {--algorithm= : Key algorithm (e.g. rsa-4096, ecdsa-p256, ed25519)}
        {--ca= : Certificate Authority ID}
        {--tenant= : Tenant ID}
        {--usage=certificate : Key usage (e.g. certificate, signing)}';

    protected $description = 'Generate a new cryptographic key pair';

    public function handle(KeyManagerInterface $keyManager): int
    {
        $algorithmValue = $this->option('algorithm');

        if ($algorithmValue === null) {
            $choices = array_map(
                static fn (KeyAlgorithm $algo): string => $algo->slug,
                KeyAlgorithm::cases(),
            );

            $algorithmValue = $this->choice(
                'Select a key algorithm',
                $choices,
                'rsa-4096',
            );
        }

        $algorithm = KeyAlgorithm::from($algorithmValue);

        $params = [];

        $caId = $this->option('ca');
        if ($caId !== null) {
            $params['ca_id'] = $caId;
        }

        $usage = $this->option('usage');
        if ($usage !== null) {
            $params['usage'] = $usage;
        }

        $tenantId = $this->option('tenant');

        $this->info("Generating {$algorithm->slug} key pair...");

        $key = $keyManager->generate(
            algorithm: $algorithm,
            params: $params,
            tenantId: $tenantId,
        );

        $this->info('Key generated successfully.');
        $this->table(
            ['Field', 'Value'],
            [
                ['UUID', $key->uuid],
                ['Algorithm', $key->algorithm->slug],
                ['Fingerprint', $key->fingerprint_sha256],
                ['Status', $key->status->slug],
                ['Usage', $key->usage],
            ],
        );

        return self::SUCCESS;
    }
}
