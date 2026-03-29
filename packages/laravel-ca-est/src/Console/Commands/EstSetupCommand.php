<?php

declare(strict_types=1);

namespace CA\Est\Console\Commands;

use CA\Models\CertificateAuthority;
use Illuminate\Console\Command;

class EstSetupCommand extends Command
{
    protected $signature = 'ca:est:setup
        {ca_uuid : The UUID of the Certificate Authority}
        {--label= : The EST label/alias for this CA}
        {--basic-auth : Enable HTTP Basic authentication}
        {--cert-auth : Enable client certificate authentication}
        {--no-basic-auth : Disable HTTP Basic authentication}
        {--no-cert-auth : Disable client certificate authentication}';

    protected $description = 'Enable and configure EST protocol for a Certificate Authority';

    public function handle(): int
    {
        $caUuid = $this->argument('ca_uuid');

        $ca = CertificateAuthority::find($caUuid);

        if ($ca === null) {
            $this->error("Certificate Authority not found: {$caUuid}");

            return self::FAILURE;
        }

        $metadata = $ca->metadata ?? [];
        $metadata['est_enabled'] = true;

        // Set EST label
        $label = $this->option('label');
        if ($label !== null) {
            $metadata['est_label'] = $label;
            $this->info("EST label set to: {$label}");
        } elseif (!isset($metadata['est_label'])) {
            $metadata['est_label'] = $caUuid;
            $this->info("EST label defaulting to CA UUID: {$caUuid}");
        }

        // Configure authentication methods
        if ($this->option('basic-auth')) {
            $metadata['est_basic_auth'] = true;
            $this->info('HTTP Basic authentication: enabled');
        } elseif ($this->option('no-basic-auth')) {
            $metadata['est_basic_auth'] = false;
            $this->info('HTTP Basic authentication: disabled');
        }

        if ($this->option('cert-auth')) {
            $metadata['est_cert_auth'] = true;
            $this->info('Client certificate authentication: enabled');
        } elseif ($this->option('no-cert-auth')) {
            $metadata['est_cert_auth'] = false;
            $this->info('Client certificate authentication: disabled');
        }

        $ca->update(['metadata' => $metadata]);

        $this->info('');
        $this->info("EST enabled for CA: {$ca->subject_dn['CN'] ?? $caUuid}");

        $prefix = config('ca-est.route_prefix', '.well-known/est');
        $estLabel = $metadata['est_label'];

        $this->info('');
        $this->info('EST Endpoints:');
        $this->table(
            ['Operation', 'Method', 'URL'],
            [
                ['CA Certs', 'GET', "/{$prefix}/{$estLabel}/cacerts"],
                ['Simple Enroll', 'POST', "/{$prefix}/{$estLabel}/simpleenroll"],
                ['Simple Re-enroll', 'POST', "/{$prefix}/{$estLabel}/simplereenroll"],
                ['Server Key Gen', 'POST', "/{$prefix}/{$estLabel}/serverkeygen"],
                ['Full CMC', 'POST', "/{$prefix}/{$estLabel}/fullcmc"],
                ['CSR Attributes', 'GET', "/{$prefix}/{$estLabel}/csrattrs"],
            ],
        );

        return self::SUCCESS;
    }
}
