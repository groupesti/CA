<?php

declare(strict_types=1);

namespace CA\Key\Console\Commands;

use CA\Key\Contracts\KeyManagerInterface;
use CA\Key\Models\Key;
use Illuminate\Console\Command;

class KeyRotateCommand extends Command
{
    protected $signature = 'ca:key:rotate {uuid : UUID of the key to rotate}';

    protected $description = 'Rotate a cryptographic key, replacing it with a newly generated key';

    public function handle(KeyManagerInterface $keyManager): int
    {
        $uuid = $this->argument('uuid');
        $key = Key::where('uuid', $uuid)->first();

        if ($key === null) {
            $this->error("Key not found: {$uuid}");

            return self::FAILURE;
        }

        $this->info("Current key: {$key->uuid}");
        $this->info("Algorithm:   {$key->algorithm->slug}");
        $this->info("Fingerprint: {$key->fingerprint_sha256}");

        if (! $this->confirm('Are you sure you want to rotate this key? The old key will be marked as rotated.')) {
            $this->info('Rotation cancelled.');

            return self::SUCCESS;
        }

        $newKey = $keyManager->rotate($key);

        $this->info('Key rotated successfully.');
        $this->newLine();
        $this->table(
            ['', 'Old Key', 'New Key'],
            [
                ['UUID', $key->uuid, $newKey->uuid],
                ['Fingerprint', $key->fingerprint_sha256, $newKey->fingerprint_sha256],
                ['Status', $key->fresh()?->status->slug ?? 'rotated', $newKey->status->slug],
            ],
        );

        return self::SUCCESS;
    }
}
