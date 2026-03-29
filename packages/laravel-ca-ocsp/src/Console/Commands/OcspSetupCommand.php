<?php

declare(strict_types=1);

namespace CA\OCSP\Console\Commands;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Models\KeyAlgorithm;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Models\CertificateAuthority;
use CA\OCSP\Models\OcspResponderCertificate;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OcspSetupCommand extends Command
{
    protected $signature = 'ca:ocsp:setup {ca_uuid : The UUID of the Certificate Authority}';

    protected $description = 'Create an OCSP responder certificate for a Certificate Authority';

    public function handle(
        KeyManagerInterface $keyManager,
        CertificateManagerInterface $certManager,
    ): int {
        $caUuid = $this->argument('ca_uuid');

        $ca = CertificateAuthority::find($caUuid);

        if ($ca === null) {
            $this->error("Certificate Authority with UUID '{$caUuid}' not found.");

            return self::FAILURE;
        }

        $this->info("Setting up OCSP responder for CA: {$ca->id}");

        // Check for existing active responder
        $existing = OcspResponderCertificate::query()
            ->forCa($ca->id)
            ->active()
            ->first();

        if ($existing !== null) {
            if (!$this->confirm('An active OCSP responder certificate already exists. Replace it?')) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }

            $existing->update(['is_active' => false]);
            $this->info('Deactivated existing responder certificate.');
        }

        // Generate a new key pair for the OCSP responder
        $this->info('Generating OCSP responder key pair...');

        $algorithm = KeyAlgorithm::tryFrom($ca->key_algorithm) ?? KeyAlgorithm::RSA_2048;

        $key = $keyManager->generate(
            algorithm: $algorithm,
            params: ['usage' => 'ocsp_signing'],
            tenantId: $ca->tenant_id,
        );

        $this->info("Generated key: {$key->id}");

        // Issue OCSP responder certificate
        $this->info('Issuing OCSP responder certificate...');

        $validityDays = (int) config('ca-ocsp.responder_certificate_validity_days', 30);
        $notBefore = Carbon::now();
        $notAfter = $notBefore->copy()->addDays($validityDays);

        $subjectDn = $ca->subject_dn;
        if (is_array($subjectDn)) {
            $subjectDn['CN'] = ($subjectDn['CN'] ?? 'CA') . ' OCSP Responder';
        }

        try {
            $certificate = $certManager->issue([
                'ca_id' => $ca->id,
                'tenant_id' => $ca->tenant_id,
                'key_id' => $key->id,
                'type' => 'ocsp_responder',
                'subject_dn' => $subjectDn,
                'not_before' => $notBefore,
                'not_after' => $notAfter,
                'extended_key_usage' => ['1.3.6.1.5.5.7.3.9'], // id-kp-OCSPSigning
                'extensions' => [
                    'id-pkix-ocsp-nocheck' => true, // 1.3.6.1.5.5.7.48.1.5
                ],
            ]);
        } catch (\Throwable $e) {
            $this->error('Failed to issue OCSP responder certificate: ' . $e->getMessage());

            return self::FAILURE;
        }

        // Create the responder certificate record
        OcspResponderCertificate::create([
            'ca_id' => $ca->id,
            'tenant_id' => $ca->tenant_id,
            'certificate_id' => $certificate->id,
            'key_id' => $key->id,
            'is_active' => true,
        ]);

        $this->info('OCSP responder certificate created successfully.');
        $this->table(
            ['Field', 'Value'],
            [
                ['CA', $ca->id],
                ['Certificate ID', $certificate->id],
                ['Key ID', $key->id],
                ['Valid From', $notBefore->toDateTimeString()],
                ['Valid Until', $notAfter->toDateTimeString()],
                ['EKU', 'id-kp-OCSPSigning (1.3.6.1.5.5.7.3.9)'],
            ],
        );

        return self::SUCCESS;
    }
}
