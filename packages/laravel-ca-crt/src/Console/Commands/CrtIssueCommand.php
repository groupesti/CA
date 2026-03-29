<?php

declare(strict_types=1);

namespace CA\Crt\Console\Commands;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Csr\Models\Csr;
use CA\DTOs\CertificateOptions;
use CA\DTOs\DistinguishedName;
use CA\Models\CertificateType;
use CA\Models\HashAlgorithm;
use CA\Key\Models\Key;
use CA\Models\CertificateAuthority;
use Illuminate\Console\Command;

final class CrtIssueCommand extends Command
{
    protected $signature = 'ca:crt:issue
        {--ca= : Certificate Authority ID}
        {--type= : Certificate type (server_tls, client_mtls, code_signing, smime, root_ca, intermediate_ca)}
        {--csr= : CSR ID to issue from}
        {--key= : Key ID for direct issuance}
        {--cn= : Common Name}
        {--san=* : Subject Alternative Names (DNS:example.com or IP:1.2.3.4)}
        {--template= : Template ID}
        {--days= : Validity period in days}
        {--hash= : Hash algorithm (sha256, sha384, sha512)}';

    protected $description = 'Issue a new X.509 certificate';

    public function handle(CertificateManagerInterface $manager): int
    {
        $caId = $this->option('ca') ?? $this->ask('Certificate Authority ID');
        $ca = CertificateAuthority::find($caId);

        if ($ca === null) {
            $this->error('Certificate Authority not found: ' . $caId);
            return self::FAILURE;
        }

        $type = $this->option('type') ?? $this->choice(
            'Certificate type',
            array_column(CertificateType::cases(), 'slug'),
            'server_tls',
        );

        $certificateType = CertificateType::from($type);
        $days = (int) ($this->option('days') ?? config('ca-crt.default_validity_days', 365));
        $hash = $this->option('hash') ?? (string) config('ca-crt.default_hash', 'sha256');

        // Parse SANs
        $sanOptions = $this->option('san');
        $sans = [];

        foreach ($sanOptions as $san) {
            if (str_starts_with($san, 'DNS:')) {
                $sans[] = ['dNSName' => substr($san, 4)];
            } elseif (str_starts_with($san, 'IP:')) {
                $sans[] = ['iPAddress' => substr($san, 3)];
            } elseif (str_starts_with($san, 'EMAIL:')) {
                $sans[] = ['rfc822Name' => substr($san, 6)];
            } else {
                $sans[] = ['dNSName' => $san];
            }
        }

        $options = new CertificateOptions(
            type: $certificateType,
            validityDays: $days,
            hashAlgorithm: HashAlgorithm::from($hash),
            subjectAlternativeNames: $sans !== [] ? $sans : null,
            templateId: $this->option('template'),
        );

        try {
            if ($this->option('csr')) {
                $csr = Csr::findOrFail($this->option('csr'));
                $certificate = $manager->issueFromCsr($ca, $csr, $options);
            } elseif ($certificateType === CertificateType::ROOT_CA) {
                $key = Key::findOrFail($this->option('key') ?? $this->ask('Key ID'));
                $certificate = $manager->issueSelfSigned($ca, $key, $options);
            } elseif ($certificateType === CertificateType::INTERMEDIATE_CA) {
                $key = Key::findOrFail($this->option('key') ?? $this->ask('Key ID'));
                $cn = $this->option('cn') ?? $this->ask('Common Name');
                $dn = new DistinguishedName(commonName: $cn);
                $certificate = $manager->issueIntermediate($ca, $dn, $key, $options);
            } else {
                $this->error('Non-CA certificate types require a CSR (--csr).');
                return self::FAILURE;
            }

            $this->info('Certificate issued successfully.');
            $this->table(
                ['Field', 'Value'],
                [
                    ['UUID', $certificate->uuid],
                    ['Serial', $certificate->serial_number],
                    ['Type', $certificate->type->slug],
                    ['Subject', json_encode($certificate->subject_dn)],
                    ['Fingerprint', $certificate->fingerprint_sha256],
                    ['Not Before', $certificate->not_before->toIso8601String()],
                    ['Not After', $certificate->not_after->toIso8601String()],
                ],
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to issue certificate: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
