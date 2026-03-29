<?php

declare(strict_types=1);

namespace CA\Crt\Console\Commands;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Models\Certificate;
use CA\Crt\Services\CertificateExporter;
use CA\Crt\Services\ChainBuilder;
use CA\Models\ExportFormat;
use Illuminate\Console\Command;

final class CrtExportCommand extends Command
{
    protected $signature = 'ca:crt:export
        {uuid : Certificate UUID to export}
        {--format=pem : Export format (pem, der, pkcs7)}
        {--output= : Output file path}
        {--chain : Include the full certificate chain}';

    protected $description = 'Export a certificate';

    public function handle(
        CertificateManagerInterface $manager,
        CertificateExporter $exporter,
        ChainBuilder $chainBuilder,
    ): int {
        $certificate = Certificate::query()
            ->where('uuid', $this->argument('uuid'))
            ->first();

        if ($certificate === null) {
            $this->error('Certificate not found: ' . $this->argument('uuid'));
            return self::FAILURE;
        }

        $format = ExportFormat::from($this->option('format'));

        try {
            if ($this->option('chain') && $format === ExportFormat::PEM) {
                $chain = $chainBuilder->buildChain($certificate);
                $output = $exporter->exportChainPem($chain);
            } else {
                $output = $manager->export($certificate, $format);
            }

            $outputPath = $this->option('output');

            if ($outputPath !== null) {
                file_put_contents($outputPath, $output);
                $this->info("Certificate exported to: {$outputPath}");
            } else {
                $this->line($output);
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to export certificate: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
