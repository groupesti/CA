<?php

declare(strict_types=1);

namespace CA\Policy\Console\Commands;

use CA\Models\CertificateAuthority;
use CA\Policy\Models\CertificatePolicy;
use Illuminate\Console\Command;

class PolicyCreateCommand extends Command
{
    protected $signature = 'ca:policy:create {ca_uuid : The UUID of the Certificate Authority}';

    protected $description = 'Create a certificate policy interactively';

    public function handle(): int
    {
        $caUuid = $this->argument('ca_uuid');
        $ca = CertificateAuthority::find($caUuid);

        if ($ca === null) {
            $this->error("Certificate Authority '{$caUuid}' not found.");

            return self::FAILURE;
        }

        $name = $this->ask('Policy name');
        $policyOid = $this->ask('Policy OID (e.g., 2.16.840.1.101.2.1)');
        $cpsUri = $this->ask('CPS URI (optional)', '');
        $description = $this->ask('Description (optional)', '');
        $isDefault = $this->confirm('Set as default policy?', false);

        $policy = CertificatePolicy::create([
            'ca_id' => $ca->id,
            'tenant_id' => $ca->tenant_id,
            'name' => $name,
            'policy_oid' => $policyOid,
            'cps_uri' => $cpsUri ?: null,
            'description' => $description ?: null,
            'is_default' => $isDefault,
            'enabled' => true,
        ]);

        $this->info("Policy '{$policy->name}' created with ID: {$policy->id}");

        return self::SUCCESS;
    }
}
