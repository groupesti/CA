<?php

declare(strict_types=1);

namespace CA\Key\Console\Commands;

use CA\Models\ExportFormat;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Key\Models\Key;
use Illuminate\Console\Command;

class KeyExportCommand extends Command
{
    protected $signature = 'ca:key:export {uuid : UUID of the key to export}
        {--format=pem : Export format (pem, der)}
        {--output= : Output file path}
        {--passphrase= : Passphrase for encrypted export}
        {--private : Export private key (requires confirmation)}';

    protected $description = 'Export a cryptographic key';

    public function handle(KeyManagerInterface $keyManager): int
    {
        $uuid = $this->argument('uuid');
        $key = Key::where('uuid', $uuid)->first();

        if ($key === null) {
            $this->error("Key not found: {$uuid}");

            return self::FAILURE;
        }

        $exportPrivate = (bool) $this->option('private');

        if ($exportPrivate && ! $this->confirm('Are you sure you want to export the private key? This is a sensitive operation.')) {
            $this->info('Export cancelled.');

            return self::SUCCESS;
        }

        $format = ExportFormat::from($this->option('format'));
        $passphrase = $this->option('passphrase');
        $outputPath = $this->option('output');

        if ($exportPrivate) {
            $exported = $keyManager->export($key, $format, $passphrase);
        } else {
            $exported = $key->public_key_pem;

            if ($format === ExportFormat::DER) {
                $pem = preg_replace('/-----[A-Z\s]+-----/', '', $exported);
                $pem = preg_replace('/\s+/', '', (string) $pem);
                $exported = base64_decode((string) $pem, true) ?: '';
            }
        }

        if ($outputPath !== null) {
            file_put_contents($outputPath, $exported);
            $this->info("Key exported to: {$outputPath}");
        } else {
            if ($format === ExportFormat::DER) {
                $this->info(base64_encode($exported));
            } else {
                $this->line($exported);
            }
        }

        return self::SUCCESS;
    }
}
