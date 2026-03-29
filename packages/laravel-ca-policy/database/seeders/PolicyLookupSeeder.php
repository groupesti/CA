<?php

declare(strict_types=1);

namespace CA\Policy\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PolicyLookupSeeder extends Seeder
{
    public function run(): void
    {
        $entries = array_merge(
            $this->policySeverities(),
            $this->policyActions(),
            $this->nameTypes(),
        );

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

    private function policySeverities(): array
    {
        return [
            [
                'type' => 'policy_severity',
                'slug' => 'error',
                'name' => 'Error',
                'description' => 'Error severity - policy violation blocks the operation',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'policy_severity',
                'slug' => 'warning',
                'name' => 'Warning',
                'description' => 'Warning severity - policy issue is flagged but does not block',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'policy_severity',
                'slug' => 'info',
                'name' => 'Info',
                'description' => 'Informational severity - for audit and logging purposes',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function policyActions(): array
    {
        return [
            [
                'type' => 'policy_action',
                'slug' => 'allow',
                'name' => 'Allow',
                'description' => 'Allow the operation to proceed',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'policy_action',
                'slug' => 'deny',
                'name' => 'Deny',
                'description' => 'Deny the operation',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'policy_action',
                'slug' => 'require_approval',
                'name' => 'Require Approval',
                'description' => 'Operation requires manual approval before proceeding',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function nameTypes(): array
    {
        return [
            [
                'type' => 'name_type',
                'slug' => 'dns',
                'name' => 'DNS',
                'description' => 'DNS hostname',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'name_type',
                'slug' => 'email',
                'name' => 'Email',
                'description' => 'Email address (RFC 822)',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'name_type',
                'slug' => 'ip',
                'name' => 'IP',
                'description' => 'IP address',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'name_type',
                'slug' => 'uri',
                'name' => 'URI',
                'description' => 'Uniform Resource Identifier',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'name_type',
                'slug' => 'directoryName',
                'name' => 'Directory Name',
                'description' => 'X.500 directory name',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }
}
