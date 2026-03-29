<?php

declare(strict_types=1);

namespace CA\Policy\Console\Commands;

use CA\Models\CertificateAuthority;
use CA\Models\NameType;
use CA\Policy\Models\CertificatePolicy;
use CA\Policy\Models\NameConstraint;
use Illuminate\Console\Command;

class NameConstraintAddCommand extends Command
{
    protected $signature = 'ca:policy:constraint
        {ca_uuid : The UUID of the Certificate Authority}
        {--type=permitted : Constraint type (permitted or excluded)}
        {--name-type=dns : Name type (dns, email, ip, uri, directoryName)}
        {--value= : Constraint value (e.g., .example.com, 10.0.0.0/8)}
        {--excluded : Set as excluded constraint}';

    protected $description = 'Add a name constraint to a CA policy';

    public function handle(): int
    {
        $caUuid = $this->argument('ca_uuid');
        $ca = CertificateAuthority::find($caUuid);

        if ($ca === null) {
            $this->error("Certificate Authority '{$caUuid}' not found.");

            return self::FAILURE;
        }

        // Find the default policy for this CA.
        $policy = CertificatePolicy::where('ca_id', $ca->id)
            ->where('is_default', true)
            ->where('enabled', true)
            ->first();

        if ($policy === null) {
            $this->error('No default enabled policy found for this CA. Create a policy first.');

            return self::FAILURE;
        }

        $constraintType = $this->option('excluded') ? 'excluded' : $this->option('type');

        $nameTypeValue = $this->option('name-type');
        $validNameTypes = [NameType::DNS, NameType::EMAIL, NameType::IP, NameType::URI, NameType::DIRECTORY];

        if (!in_array($nameTypeValue, $validNameTypes, true)) {
            $this->error("Invalid name type: {$nameTypeValue}. Valid: " . implode(', ', $validNameTypes));

            return self::FAILURE;
        }

        $nameType = $nameTypeValue;

        $value = $this->option('value');

        if ($value === null || $value === '') {
            $value = $this->ask('Constraint value (e.g., .example.com, 10.0.0.0/8)');
        }

        $constraint = NameConstraint::create([
            'policy_id' => $policy->id,
            'ca_id' => $ca->id,
            'type' => $constraintType,
            'name_type' => $nameType,
            'value' => $value,
            'enabled' => true,
        ]);

        $this->info("Name constraint added: {$constraintType} {$nameType} '{$value}' (ID: {$constraint->id})");

        return self::SUCCESS;
    }
}
