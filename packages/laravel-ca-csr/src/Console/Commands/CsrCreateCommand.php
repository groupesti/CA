<?php

declare(strict_types=1);

namespace CA\Csr\Console\Commands;

use CA\Csr\Contracts\CsrManagerInterface;
use CA\DTOs\DistinguishedName;
use CA\Key\Models\Key;
use CA\Models\CertificateTemplate;
use Illuminate\Console\Command;

class CsrCreateCommand extends Command
{
    protected $signature = 'ca:csr:create
        {--ca= : Certificate Authority ID}
        {--key= : Key ID}
        {--cn= : Common Name}
        {--san=* : Subject Alternative Names (type:value, e.g. dns:example.com)}
        {--template= : Certificate Template ID}';

    protected $description = 'Create a new Certificate Signing Request';

    public function handle(CsrManagerInterface $manager): int
    {
        $cn = $this->option('cn') ?? $this->ask('Common Name (CN)');
        $keyId = $this->option('key') ?? $this->ask('Key ID');

        if ($cn === null || $keyId === null) {
            $this->error('Common Name and Key ID are required.');
            return self::FAILURE;
        }

        $key = Key::find($keyId);

        if ($key === null) {
            $this->error("Key with ID '{$keyId}' not found.");
            return self::FAILURE;
        }

        $dn = new DistinguishedName(commonName: $cn);

        $extensions = [];
        foreach ($this->option('san') as $san) {
            $parts = explode(':', $san, 2);
            if (count($parts) === 2) {
                $extensions[] = ['type' => $parts[0], 'value' => $parts[1]];
            }
        }

        $template = null;
        $templateId = $this->option('template');
        if ($templateId !== null) {
            $template = CertificateTemplate::find($templateId);
            if ($template === null) {
                $this->error("Template with ID '{$templateId}' not found.");
                return self::FAILURE;
            }
        }

        $csr = $manager->create($dn, $key, $extensions, $template);

        $this->info("CSR created successfully.");
        $this->table(
            ['Field', 'Value'],
            [
                ['UUID', $csr->uuid],
                ['Subject CN', $cn],
                ['Status', $csr->status],
                ['Expires', $csr->expires_at?->toDateTimeString() ?? 'N/A'],
            ],
        );

        return self::SUCCESS;
    }
}
