<?php

declare(strict_types=1);

namespace CA\Tsa\Console\Commands;

use CA\Tsa\Models\TimestampToken;
use Illuminate\Console\Command;

/**
 * List recent timestamp tokens.
 */
class TsaTokenListCommand extends Command
{
    protected $signature = 'ca:tsa:tokens
        {--ca= : Filter by CA UUID}
        {--limit=20 : Number of tokens to show}';

    protected $description = 'List recent timestamp tokens';

    public function handle(): int
    {
        $query = TimestampToken::query()->orderByDesc('gen_time');

        $caId = $this->option('ca');
        if ($caId !== null) {
            $query->where('ca_id', $caId);
        }

        $limit = (int) $this->option('limit');
        $tokens = $query->limit($limit)->get();

        if ($tokens->isEmpty()) {
            $this->info('No timestamp tokens found.');

            return self::SUCCESS;
        }

        $this->info("Timestamp Tokens (showing {$tokens->count()}):");

        $rows = [];
        foreach ($tokens as $token) {
            $rows[] = [
                $token->serial_number,
                $token->hash_algorithm,
                substr($token->message_imprint, 0, 16) . '...',
                $token->policy_oid,
                $token->gen_time?->toIso8601String() ?? 'N/A',
                $token->nonce ?? 'None',
                $token->ip_address ?? 'N/A',
            ];
        }

        $this->table(
            ['Serial', 'Hash Alg', 'Imprint', 'Policy', 'Gen Time', 'Nonce', 'IP'],
            $rows,
        );

        return self::SUCCESS;
    }
}
