<?php

declare(strict_types=1);

namespace CA\Key\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KeyLookupSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            [
                'type' => 'key_status',
                'slug' => 'active',
                'name' => 'Active',
                'description' => 'Key is active and in use',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_status',
                'slug' => 'rotated',
                'name' => 'Rotated',
                'description' => 'Key has been rotated and replaced',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_status',
                'slug' => 'compromised',
                'name' => 'Compromised',
                'description' => 'Key has been compromised',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_status',
                'slug' => 'destroyed',
                'name' => 'Destroyed',
                'description' => 'Key has been securely destroyed',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_status',
                'slug' => 'expired',
                'name' => 'Expired',
                'description' => 'Key has expired',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
        ];

        foreach ($entries as $entry) {
            DB::table('ca_lookups')->updateOrInsert(
                ['type' => $entry['type'], 'slug' => $entry['slug']],
                array_merge($entry, [
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]),
            );
        }
    }
}
