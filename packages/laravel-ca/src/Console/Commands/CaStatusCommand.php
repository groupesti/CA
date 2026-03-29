<?php

declare(strict_types=1);

namespace CA\Console\Commands;

use CA\Models\CertificateAuthority;
use CA\Services\CaManager;
use Illuminate\Console\Command;

class CaStatusCommand extends Command
{
    protected $signature = 'ca:status {uuid : The UUID of the Certificate Authority}';

    protected $description = 'Show detailed status and hierarchy of a Certificate Authority';

    public function handle(CaManager $caManager): int
    {
        $uuid = $this->argument('uuid');

        $ca = CertificateAuthority::find($uuid);

        if ($ca === null) {
            $this->error("Certificate Authority [{$uuid}] not found.");
            return self::FAILURE;
        }

        $this->info("Certificate Authority Details");
        $this->newLine();

        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $ca->id],
                ['Common Name', $ca->subject_dn['CN'] ?? '-'],
                ['Subject DN', $this->formatSubjectDn($ca->subject_dn)],
                ['Type', $ca->type->slug],
                ['Status', $ca->status->slug],
                ['Serial Number', $ca->serial_number],
                ['Key Algorithm', $ca->key_algorithm],
                ['Hash Algorithm', $ca->hash_algorithm],
                ['Path Length', $ca->path_length !== null ? (string) $ca->path_length : 'Unlimited'],
                ['Is Root', $ca->isRoot() ? 'Yes' : 'No'],
                ['Chain Depth', (string) $ca->getChainDepth()],
                ['Parent ID', $ca->parent_id ?? 'None (Root)'],
                ['Tenant ID', $ca->tenant_id ?? 'None'],
                ['Not Before', $ca->not_before?->toDateTimeString() ?? '-'],
                ['Not After', $ca->not_after?->toDateTimeString() ?? '-'],
                ['Children', (string) $ca->children()->count()],
                ['Created', $ca->created_at?->toDateTimeString() ?? '-'],
                ['Updated', $ca->updated_at?->toDateTimeString() ?? '-'],
            ],
        );

        $hierarchy = $caManager->getHierarchy($ca);

        $this->newLine();
        $this->info('Hierarchy Tree:');
        $this->newLine();
        $this->renderTree($hierarchy, '');

        return self::SUCCESS;
    }

    private function renderTree(array $node, string $prefix): void
    {
        $statusIcon = match ($node['status']) {
            'active' => '[ACTIVE]',
            'suspended' => '[SUSPENDED]',
            'revoked' => '[REVOKED]',
            'expired' => '[EXPIRED]',
            default => '[' . strtoupper($node['status']) . ']',
        };

        $cn = $node['subject_dn']['CN'] ?? 'Unknown';
        $type = strtoupper($node['type']);

        $this->line("{$prefix}{$cn} ({$type}) {$statusIcon}");

        $children = $node['children'] ?? [];
        $count = count($children);

        foreach ($children as $index => $child) {
            $isLast = $index === $count - 1;
            $connector = $isLast ? '└── ' : '├── ';
            $childPrefix = $isLast ? '    ' : '│   ';

            $this->renderTree($child, $prefix . $connector);

            if (!empty($child['children'])) {
                // Children will handle their own prefixing
            }
        }
    }

    private function formatSubjectDn(array $dn): string
    {
        $parts = [];

        foreach ($dn as $key => $value) {
            $parts[] = "{$key}={$value}";
        }

        return implode(', ', $parts);
    }
}
