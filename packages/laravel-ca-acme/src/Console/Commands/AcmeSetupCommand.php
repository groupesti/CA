<?php

declare(strict_types=1);

namespace CA\Acme\Console\Commands;

use CA\Models\CertificateAuthority;
use Illuminate\Console\Command;

class AcmeSetupCommand extends Command
{
    protected $signature = 'ca:acme:setup
        {--ca= : The CA ID to use for ACME}
        {--tos-url= : Terms of service URL}
        {--website-url= : Website URL}
        {--prefix= : Route prefix for ACME endpoints}';

    protected $description = 'Enable and configure ACME for a Certificate Authority';

    public function handle(): int
    {
        $this->info('ACME Server Setup');
        $this->line('');

        // Select CA
        $caId = $this->option('ca');

        if ($caId === null) {
            $cas = CertificateAuthority::active()->get();

            if ($cas->isEmpty()) {
                $this->error('No active Certificate Authorities found. Create one first.');

                return self::FAILURE;
            }

            $choices = $cas->mapWithKeys(
                fn(CertificateAuthority $ca): array => [$ca->id => $ca->id . ' - ' . ($ca->subject_dn['CN'] ?? 'Unknown')],
            )->toArray();

            $selected = $this->choice('Select a Certificate Authority for ACME', array_values($choices));
            $caId = array_search($selected, $choices, true);
        }

        $ca = CertificateAuthority::find($caId);

        if ($ca === null) {
            $this->error("Certificate Authority with ID '{$caId}' not found.");

            return self::FAILURE;
        }

        // Configuration summary
        $tosUrl = $this->option('tos-url') ?? config('ca-acme.terms_of_service_url');
        $websiteUrl = $this->option('website-url') ?? config('ca-acme.website_url');
        $prefix = $this->option('prefix') ?? config('ca-acme.route_prefix', 'acme');

        $this->table(['Setting', 'Value'], [
            ['Certificate Authority', $ca->id . ' (' . ($ca->subject_dn['CN'] ?? 'N/A') . ')'],
            ['Route Prefix', $prefix],
            ['Terms of Service URL', $tosUrl ?? 'Not set'],
            ['Website URL', $websiteUrl ?? 'Not set'],
            ['Challenge Types', implode(', ', config('ca-acme.challenge_types', []))],
            ['Order Validity', config('ca-acme.order_validity_hours', 168) . ' hours'],
            ['Nonce TTL', config('ca-acme.nonce_ttl_seconds', 3600) . ' seconds'],
        ]);

        $this->line('');
        $this->info('To apply these settings, update your .env file:');
        $this->line("  CA_ACME_ENABLED=true");
        $this->line("  CA_ACME_CA_ID={$ca->id}");

        if ($tosUrl !== null) {
            $this->line("  CA_ACME_TOS_URL={$tosUrl}");
        }

        if ($websiteUrl !== null) {
            $this->line("  CA_ACME_WEBSITE_URL={$websiteUrl}");
        }

        if ($prefix !== 'acme') {
            $this->line("  CA_ACME_ROUTE_PREFIX={$prefix}");
        }

        $this->line('');
        $this->info("ACME directory will be available at: " . url("{$prefix}/directory"));

        return self::SUCCESS;
    }
}
