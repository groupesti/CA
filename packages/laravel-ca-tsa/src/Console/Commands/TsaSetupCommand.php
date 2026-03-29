<?php

declare(strict_types=1);

namespace CA\Tsa\Console\Commands;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Models\KeyAlgorithm;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Models\CertificateAuthority;
use CA\Tsa\Models\TsaCertificate;
use Illuminate\Console\Command;

/**
 * Create a TSA signing certificate with extKeyUsage: id-kp-timeStamping.
 */
class TsaSetupCommand extends Command
{
    protected $signature = 'ca:tsa:setup
        {ca_uuid : The UUID of the Certificate Authority}
        {--algorithm=ecdsa-p256 : Key algorithm (rsa-2048, rsa-4096, ecdsa-p256, ecdsa-p384, ecdsa-p521, ed25519)}
        {--days=365 : Certificate validity in days}';

    protected $description = 'Create a TSA signing certificate for a Certificate Authority';

    public function handle(
        KeyManagerInterface $keyManager,
        CertificateManagerInterface $certManager,
    ): int {
        $caUuid = $this->argument('ca_uuid');
        $algorithmValue = $this->option('algorithm');
        $days = (int) $this->option('days');

        // Find the CA
        $ca = CertificateAuthority::find($caUuid);
        if ($ca === null) {
            $this->error("Certificate Authority not found: {$caUuid}");

            return self::FAILURE;
        }

        $caName = $ca->subject_dn['CN'] ?? $caUuid;
        $this->info("Setting up TSA for CA: {$caName}");

        // Deactivate any existing TSA certificates for this CA
        TsaCertificate::where('ca_id', $caUuid)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Resolve key algorithm
        $algorithm = KeyAlgorithm::tryFrom($algorithmValue);
        if ($algorithm === null) {
            $this->error("Invalid key algorithm: {$algorithmValue}");

            return self::FAILURE;
        }

        // Generate a dedicated TSA signing key
        $this->info('Generating TSA signing key...');
        $key = $keyManager->generate($algorithm, [], $ca->tenant_id);

        // Issue a TSA signing certificate with extKeyUsage: id-kp-timeStamping
        $this->info('Issuing TSA signing certificate...');

        $now = now();
        $subjectDn = $ca->subject_dn;
        $subjectDn['CN'] = ($subjectDn['CN'] ?? 'CA') . ' TSA Responder';

        $certificate = $certManager->issue([
            'ca_id' => $caUuid,
            'key_id' => $key->id,
            'subject_dn' => $subjectDn,
            'type' => 'end-entity',
            'not_before' => $now,
            'not_after' => $now->copy()->addDays($days),
            'key_usage' => ['digitalSignature'],
            'extended_key_usage' => [
                '1.3.6.1.5.5.7.3.8',  // id-kp-timeStamping
            ],
        ]);

        // Register as TSA certificate
        $tsaCert = TsaCertificate::create([
            'ca_id' => $caUuid,
            'certificate_id' => $certificate->id,
            'key_id' => $key->id,
            'is_active' => true,
        ]);

        $this->info('TSA setup complete!');
        $this->table(
            ['Field', 'Value'],
            [
                ['CA', $ca->subject_dn['CN'] ?? $caUuid],
                ['Certificate ID', $certificate->id],
                ['Key ID', $key->id],
                ['Key Algorithm', $algorithmValue],
                ['Serial Number', $certificate->serial_number],
                ['Not Before', $certificate->not_before?->toIso8601String()],
                ['Not After', $certificate->not_after?->toIso8601String()],
                ['ExtKeyUsage', 'id-kp-timeStamping (1.3.6.1.5.5.7.3.8)'],
                ['Active', 'Yes'],
            ],
        );

        return self::SUCCESS;
    }
}
