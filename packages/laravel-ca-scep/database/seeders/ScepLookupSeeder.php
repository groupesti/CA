<?php

declare(strict_types=1);

namespace CA\Scep\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScepLookupSeeder extends Seeder
{
    public function run(): void
    {
        $entries = array_merge(
            $this->messageTypes(),
            $this->pkiStatuses(),
            $this->failInfos(),
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

    private function messageTypes(): array
    {
        return [
            [
                'type' => 'scep_message_type',
                'slug' => 'cert_rep',
                'name' => 'CertRep',
                'description' => 'Certificate response message',
                'numeric_value' => 3,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_message_type',
                'slug' => 'renewal_req',
                'name' => 'RenewalReq',
                'description' => 'Certificate renewal request',
                'numeric_value' => 17,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_message_type',
                'slug' => 'pkcs_req',
                'name' => 'PKCSReq',
                'description' => 'PKCS#10 certificate request',
                'numeric_value' => 19,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_message_type',
                'slug' => 'cert_poll',
                'name' => 'CertPoll',
                'description' => 'Certificate polling request',
                'numeric_value' => 20,
                'metadata' => json_encode([]),
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_message_type',
                'slug' => 'get_cert',
                'name' => 'GetCert',
                'description' => 'Get certificate request',
                'numeric_value' => 21,
                'metadata' => json_encode([]),
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_message_type',
                'slug' => 'get_crl',
                'name' => 'GetCRL',
                'description' => 'Get CRL request',
                'numeric_value' => 22,
                'metadata' => json_encode([]),
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function pkiStatuses(): array
    {
        return [
            [
                'type' => 'scep_pki_status',
                'slug' => 'success',
                'name' => 'Success',
                'description' => 'Request completed successfully',
                'numeric_value' => 0,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_pki_status',
                'slug' => 'failure',
                'name' => 'Failure',
                'description' => 'Request failed',
                'numeric_value' => 2,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_pki_status',
                'slug' => 'pending',
                'name' => 'Pending',
                'description' => 'Request is pending',
                'numeric_value' => 3,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function failInfos(): array
    {
        return [
            [
                'type' => 'scep_fail_info',
                'slug' => 'bad_alg',
                'name' => 'Bad Algorithm',
                'description' => 'Unrecognized or unsupported algorithm',
                'numeric_value' => 0,
                'metadata' => json_encode([]),
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_fail_info',
                'slug' => 'bad_message_check',
                'name' => 'Bad Message Check',
                'description' => 'Integrity check failed',
                'numeric_value' => 1,
                'metadata' => json_encode([]),
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_fail_info',
                'slug' => 'bad_request',
                'name' => 'Bad Request',
                'description' => 'Transaction not permitted or supported',
                'numeric_value' => 2,
                'metadata' => json_encode([]),
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_fail_info',
                'slug' => 'bad_time',
                'name' => 'Bad Time',
                'description' => 'Message time field was not sufficiently close to system time',
                'numeric_value' => 3,
                'metadata' => json_encode([]),
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'scep_fail_info',
                'slug' => 'bad_cert_id',
                'name' => 'Bad Certificate ID',
                'description' => 'No certificate could be identified matching the provided criteria',
                'numeric_value' => 4,
                'metadata' => json_encode([]),
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }
}
