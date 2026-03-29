<?php

declare(strict_types=1);

namespace CA\Oid\Database\Seeders;

use CA\Oid\Models\OidEntry;
use CA\Oid\Registry\ExtendedValidationOids;
use CA\Oid\Registry\MicrosoftOids;
use CA\Oid\Registry\StandardOids;
use CA\Oid\Services\OidValidator;
use Illuminate\Database\Seeder;

class StandardOidSeeder extends Seeder
{
    public function run(): void
    {
        $validator = new OidValidator();

        $sources = [
            StandardOids::getAll(),
            MicrosoftOids::getAll(),
            ExtendedValidationOids::getAll(),
        ];

        $rows = [];
        $now = now();

        foreach ($sources as $source) {
            foreach ($source as $oid => $data) {
                $parentOid = $validator->getParent($oid);

                $rows[] = [
                    'oid' => $oid,
                    'name' => $data['name'],
                    'short_name' => $data['short_name'] ?? null,
                    'description' => $data['description'] ?? null,
                    'category' => $data['category'] ?? 'custom',
                    'is_standard' => true,
                    'parent_oid' => $parentOid,
                    'metadata' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Upsert in chunks to avoid duplicates
        foreach (array_chunk($rows, 100) as $chunk) {
            OidEntry::upsert(
                $chunk,
                ['oid'],
                ['name', 'short_name', 'description', 'category', 'is_standard', 'parent_oid', 'updated_at'],
            );
        }
    }
}
