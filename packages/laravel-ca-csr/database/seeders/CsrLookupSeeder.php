<?php

declare(strict_types=1);

namespace CA\Csr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CsrLookupSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            [
                'type' => 'csr_status',
                'slug' => 'pending',
                'name' => 'Pending',
                'description' => 'CSR is pending review',
                'numeric_value' => null,
                'metadata' => json_encode(['is_pending' => true, 'is_actionable' => true]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'csr_status',
                'slug' => 'approved',
                'name' => 'Approved',
                'description' => 'CSR has been approved',
                'numeric_value' => null,
                'metadata' => json_encode(['is_pending' => false, 'is_actionable' => true]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'csr_status',
                'slug' => 'rejected',
                'name' => 'Rejected',
                'description' => 'CSR has been rejected',
                'numeric_value' => null,
                'metadata' => json_encode(['is_pending' => false, 'is_actionable' => false]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'csr_status',
                'slug' => 'signed',
                'name' => 'Signed',
                'description' => 'CSR has been signed and certificate issued',
                'numeric_value' => null,
                'metadata' => json_encode(['is_pending' => false, 'is_actionable' => false]),
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'csr_status',
                'slug' => 'expired',
                'name' => 'Expired',
                'description' => 'CSR has expired',
                'numeric_value' => null,
                'metadata' => json_encode(['is_pending' => false, 'is_actionable' => false]),
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
