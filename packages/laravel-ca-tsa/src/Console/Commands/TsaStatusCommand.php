<?php

declare(strict_types=1);

namespace CA\Tsa\Console\Commands;

use CA\Tsa\Models\TimestampToken;
use CA\Tsa\Models\TsaCertificate;
use Illuminate\Console\Command;

/**
 * Show TSA status: active responders, token count, last timestamp.
 */
class TsaStatusCommand extends Command
{
    protected $signature = 'ca:tsa:status';

    protected $description = 'Show TSA status information';

    public function handle(): int
    {
        $this->info('TSA Status');
        $this->line('');

        // Configuration
        $this->info('Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Enabled', config('ca-tsa.enabled') ? 'Yes' : 'No'],
                ['Policy OID', config('ca-tsa.policy_oid', '1.2.3.4.1')],
                ['Default Hash', config('ca-tsa.default_hash', 'sha256')],
                ['Ordering', config('ca-tsa.ordering') ? 'Yes' : 'No'],
                ['Nonce Required', config('ca-tsa.nonce_required') ? 'Yes' : 'No'],
                ['Serial Number Bits', (string) config('ca-tsa.serial_number_bits', 64)],
                ['Route Prefix', config('ca-tsa.route_prefix', 'tsa')],
            ],
        );

        // Active responders
        $activeCerts = TsaCertificate::query()
            ->where('is_active', true)
            ->with(['certificateAuthority', 'certificate'])
            ->get();

        $this->line('');
        $this->info('Active TSA Responders: ' . $activeCerts->count());

        if ($activeCerts->isNotEmpty()) {
            $rows = [];
            foreach ($activeCerts as $tsaCert) {
                $rows[] = [
                    $tsaCert->ca_id,
                    $tsaCert->certificate?->subject_dn['CN'] ?? 'N/A',
                    $tsaCert->certificate?->serial_number ?? 'N/A',
                    $tsaCert->certificate?->not_after?->toIso8601String() ?? 'N/A',
                ];
            }

            $this->table(['CA ID', 'Subject CN', 'Serial', 'Expires'], $rows);
        }

        // Token statistics
        $totalTokens = TimestampToken::count();
        $lastToken = TimestampToken::query()->orderByDesc('gen_time')->first();

        $this->line('');
        $this->info('Timestamp Tokens:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Tokens', (string) $totalTokens],
                ['Last Timestamp', $lastToken?->gen_time?->toIso8601String() ?? 'None'],
                ['Last Serial', $lastToken?->serial_number ?? 'None'],
            ],
        );

        return self::SUCCESS;
    }
}
