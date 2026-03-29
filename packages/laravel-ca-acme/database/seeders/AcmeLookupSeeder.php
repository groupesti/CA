<?php

declare(strict_types=1);

namespace CA\Acme\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcmeLookupSeeder extends Seeder
{
    public function run(): void
    {
        $entries = array_merge(
            $this->orderStatuses(),
            $this->authorizationStatuses(),
            $this->challengeStatuses(),
            $this->challengeTypes(),
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

    private function orderStatuses(): array
    {
        return [
            [
                'type' => 'acme_order_status',
                'slug' => 'pending',
                'name' => 'Pending',
                'description' => 'Order is pending authorization',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_order_status',
                'slug' => 'ready',
                'name' => 'Ready',
                'description' => 'Order is ready for finalization',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_order_status',
                'slug' => 'processing',
                'name' => 'Processing',
                'description' => 'Order is being processed',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_order_status',
                'slug' => 'valid',
                'name' => 'Valid',
                'description' => 'Order has been fulfilled and certificate issued',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_order_status',
                'slug' => 'invalid',
                'name' => 'Invalid',
                'description' => 'Order has failed validation',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_order_status',
                'slug' => 'expired',
                'name' => 'Expired',
                'description' => 'Order has expired',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_order_status',
                'slug' => 'revoked',
                'name' => 'Revoked',
                'description' => 'Order certificate has been revoked',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 7,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function authorizationStatuses(): array
    {
        return [
            [
                'type' => 'acme_authorization_status',
                'slug' => 'pending',
                'name' => 'Pending',
                'description' => 'Authorization is pending validation',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_authorization_status',
                'slug' => 'valid',
                'name' => 'Valid',
                'description' => 'Authorization has been validated',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_authorization_status',
                'slug' => 'invalid',
                'name' => 'Invalid',
                'description' => 'Authorization validation failed',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_authorization_status',
                'slug' => 'deactivated',
                'name' => 'Deactivated',
                'description' => 'Authorization has been deactivated',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_authorization_status',
                'slug' => 'expired',
                'name' => 'Expired',
                'description' => 'Authorization has expired',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_authorization_status',
                'slug' => 'revoked',
                'name' => 'Revoked',
                'description' => 'Authorization has been revoked',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function challengeStatuses(): array
    {
        return [
            [
                'type' => 'acme_challenge_status',
                'slug' => 'pending',
                'name' => 'Pending',
                'description' => 'Challenge is pending',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_challenge_status',
                'slug' => 'processing',
                'name' => 'Processing',
                'description' => 'Challenge is being processed',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_challenge_status',
                'slug' => 'valid',
                'name' => 'Valid',
                'description' => 'Challenge has been validated',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_challenge_status',
                'slug' => 'invalid',
                'name' => 'Invalid',
                'description' => 'Challenge validation failed',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function challengeTypes(): array
    {
        return [
            [
                'type' => 'acme_challenge_type',
                'slug' => 'http-01',
                'name' => 'HTTP-01',
                'description' => 'HTTP challenge validation',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_challenge_type',
                'slug' => 'dns-01',
                'name' => 'DNS-01',
                'description' => 'DNS challenge validation',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'acme_challenge_type',
                'slug' => 'tls-alpn-01',
                'name' => 'TLS-ALPN-01',
                'description' => 'TLS ALPN challenge validation',
                'numeric_value' => null,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }
}
