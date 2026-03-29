<?php

declare(strict_types=1);

namespace CA\OCSP\Console\Commands;

use CA\Models\CertificateAuthority;
use CA\OCSP\Models\OcspResponderCertificate;
use CA\OCSP\Models\OcspResponse;
use Illuminate\Console\Command;

class OcspStatusCommand extends Command
{
    protected $signature = 'ca:ocsp:status';

    protected $description = 'Show the status of all OCSP responders';

    public function handle(): int
    {
        $this->info('OCSP Responder Status');
        $this->newLine();

        $authorities = CertificateAuthority::query()->active()->get();

        if ($authorities->isEmpty()) {
            $this->warn('No active Certificate Authorities found.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($authorities as $ca) {
            $responder = OcspResponderCertificate::query()
                ->forCa($ca->id)
                ->active()
                ->with('certificate')
                ->first();

            $responseCount = OcspResponse::query()
                ->where('ca_id', $ca->id)
                ->count();

            $goodCount = OcspResponse::query()
                ->where('ca_id', $ca->id)
                ->where('status', 'good')
                ->count();

            $revokedCount = OcspResponse::query()
                ->where('ca_id', $ca->id)
                ->where('status', 'revoked')
                ->count();

            $unknownCount = OcspResponse::query()
                ->where('ca_id', $ca->id)
                ->where('status', 'unknown')
                ->count();

            $subjectDn = $ca->subject_dn;
            $cnDisplay = is_array($subjectDn) ? ($subjectDn['CN'] ?? $ca->id) : $ca->id;

            $certExpiry = $responder?->certificate?->not_after?->toDateString() ?? 'N/A';
            $responderStatus = $responder !== null ? 'Active' : 'Not configured';

            $rows[] = [
                $cnDisplay,
                $ca->id,
                $responderStatus,
                $certExpiry,
                $responseCount,
                $goodCount,
                $revokedCount,
                $unknownCount,
            ];
        }

        $this->table(
            ['CA', 'UUID', 'Responder', 'Cert Expiry', 'Responses', 'Good', 'Revoked', 'Unknown'],
            $rows,
        );

        // Cache stats
        $this->newLine();
        $this->info('Configuration:');
        $this->line('  Enabled:        ' . (config('ca-ocsp.enabled') ? 'Yes' : 'No'));
        $this->line('  Cache TTL:      ' . config('ca-ocsp.cache_seconds') . 's');
        $this->line('  Nonce Required: ' . (config('ca-ocsp.nonce_required') ? 'Yes' : 'No'));
        $this->line('  Route Prefix:   ' . config('ca-ocsp.route_prefix'));

        return self::SUCCESS;
    }
}
