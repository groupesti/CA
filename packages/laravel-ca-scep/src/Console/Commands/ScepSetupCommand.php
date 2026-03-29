<?php

declare(strict_types=1);

namespace CA\Scep\Console\Commands;

use CA\Crt\Models\Certificate;
use CA\Models\CertificateType;
use CA\Models\CertificateAuthority;
use Illuminate\Console\Command;

final class ScepSetupCommand extends Command
{
    protected $signature = 'ca:scep:setup
        {ca_uuid : The UUID of the Certificate Authority to enable SCEP for}';

    protected $description = 'Enable and verify SCEP configuration for a Certificate Authority';

    public function handle(): int
    {
        $caUuid = $this->argument('ca_uuid');
        $ca = CertificateAuthority::find($caUuid);

        if ($ca === null) {
            $this->error('Certificate Authority not found: ' . $caUuid);
            return self::FAILURE;
        }

        $this->info('Setting up SCEP for CA: ' . json_encode($ca->subject_dn));

        // Verify CA has an active certificate
        $caCert = Certificate::query()
            ->forCa($ca->id)
            ->active()
            ->where(function ($query) {
                $query->where('type', CertificateType::ROOT_CA)
                    ->orWhere('type', CertificateType::INTERMEDIATE_CA);
            })
            ->first();

        if ($caCert === null) {
            $this->error('No active CA certificate found. SCEP requires an active CA certificate.');
            return self::FAILURE;
        }

        $this->info('Active CA certificate found: ' . $caCert->serial_number);

        // Verify CA has a key
        if ($caCert->key_id === null) {
            $this->error('CA certificate has no associated key. SCEP requires the CA private key.');
            return self::FAILURE;
        }

        $this->info('CA key verified.');

        // Verify key algorithm is RSA (SCEP requires RSA)
        if ($ca->key_algorithm !== null && !str_contains(strtolower($ca->key_algorithm), 'rsa')) {
            $this->warn('SCEP requires RSA keys. Current key algorithm: ' . $ca->key_algorithm);
            $this->warn('SCEP enrollment may fail with non-RSA keys.');
        }

        // Display configuration
        $this->newLine();
        $this->info('SCEP Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Enabled', config('ca-scep.enabled') ? 'Yes' : 'No'],
                ['Route Prefix', config('ca-scep.route_prefix', 'scep')],
                ['Challenge Required', config('ca-scep.challenge_password_required') ? 'Yes' : 'No'],
                ['Challenge TTL', config('ca-scep.challenge_password_ttl', 3600) . ' seconds'],
                ['Auto Approve', config('ca-scep.auto_approve') ? 'Yes' : 'No'],
                ['Capabilities', implode(', ', config('ca-scep.capabilities', []))],
            ],
        );

        // Display SCEP endpoint URL
        $routePrefix = config('ca-scep.route_prefix', 'scep');
        $this->newLine();
        $this->info('SCEP Endpoint URL:');
        $this->line("  {$routePrefix}/{$ca->id}/pkiclient.exe");
        $this->newLine();
        $this->info('SCEP setup complete. Devices can now enroll using the endpoint above.');

        return self::SUCCESS;
    }
}
