<?php

declare(strict_types=1);

namespace CA\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreLookupSeeder extends Seeder
{
    public function run(): void
    {
        $entries = array_merge(
            $this->keyAlgorithms(),
            $this->keyStatuses(),
            $this->certificateTypes(),
            $this->certificateStatuses(),
            $this->revocationReasons(),
            $this->hashAlgorithms(),
            $this->exportFormats(),
            $this->csrStatuses(),
            $this->scepMessageTypes(),
            $this->scepPkiStatuses(),
            $this->scepFailInfos(),
            $this->acmeOrderStatuses(),
            $this->acmeChallengeStatuses(),
            $this->acmeChallengeTypes(),
            $this->acmeAuthorizationStatuses(),
            $this->policySeverities(),
            $this->policyActions(),
            $this->nameTypes(),
        );

        foreach ($entries as $entry) {
            DB::table('ca_lookups')->updateOrInsert(
                ['type' => $entry['type'], 'slug' => $entry['slug']],
                array_merge($entry, [
                    'metadata' => isset($entry['metadata']) ? json_encode($entry['metadata']) : null,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]),
            );
        }
    }

    private function keyAlgorithms(): array
    {
        return [
            [
                'type' => 'key_algorithm',
                'slug' => 'rsa-2048',
                'name' => 'RSA 2048-bit',
                'description' => 'RSA key with 2048-bit modulus',
                'numeric_value' => null,
                'metadata' => ['key_size' => 2048, 'curve' => null, 'is_rsa' => true, 'is_ec' => false, 'is_eddsa' => false],
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_algorithm',
                'slug' => 'rsa-4096',
                'name' => 'RSA 4096-bit',
                'description' => 'RSA key with 4096-bit modulus',
                'numeric_value' => null,
                'metadata' => ['key_size' => 4096, 'curve' => null, 'is_rsa' => true, 'is_ec' => false, 'is_eddsa' => false],
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_algorithm',
                'slug' => 'ecdsa-p256',
                'name' => 'ECDSA P-256',
                'description' => 'Elliptic Curve Digital Signature Algorithm with P-256 curve',
                'numeric_value' => null,
                'metadata' => ['key_size' => 256, 'curve' => 'secp256r1', 'is_rsa' => false, 'is_ec' => true, 'is_eddsa' => false],
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_algorithm',
                'slug' => 'ecdsa-p384',
                'name' => 'ECDSA P-384',
                'description' => 'Elliptic Curve Digital Signature Algorithm with P-384 curve',
                'numeric_value' => null,
                'metadata' => ['key_size' => 384, 'curve' => 'secp384r1', 'is_rsa' => false, 'is_ec' => true, 'is_eddsa' => false],
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_algorithm',
                'slug' => 'ecdsa-p521',
                'name' => 'ECDSA P-521',
                'description' => 'Elliptic Curve Digital Signature Algorithm with P-521 curve',
                'numeric_value' => null,
                'metadata' => ['key_size' => 521, 'curve' => 'secp521r1', 'is_rsa' => false, 'is_ec' => true, 'is_eddsa' => false],
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_algorithm',
                'slug' => 'ed25519',
                'name' => 'Ed25519',
                'description' => 'Edwards-curve Digital Signature Algorithm with Curve25519',
                'numeric_value' => null,
                'metadata' => ['key_size' => 256, 'curve' => 'Ed25519', 'is_rsa' => false, 'is_ec' => false, 'is_eddsa' => true],
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function keyStatuses(): array
    {
        return [
            [
                'type' => 'key_status',
                'slug' => 'active',
                'name' => 'Active',
                'description' => 'Key is active and available for use',
                'numeric_value' => null,
                'metadata' => ['is_usable' => true],
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_status',
                'slug' => 'rotated',
                'name' => 'Rotated',
                'description' => 'Key has been rotated and replaced by a newer key',
                'numeric_value' => null,
                'metadata' => ['is_usable' => false],
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_status',
                'slug' => 'destroyed',
                'name' => 'Destroyed',
                'description' => 'Key material has been permanently destroyed',
                'numeric_value' => null,
                'metadata' => ['is_usable' => false],
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_status',
                'slug' => 'suspended',
                'name' => 'Suspended',
                'description' => 'Key is temporarily suspended from use',
                'numeric_value' => null,
                'metadata' => ['is_usable' => false],
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'key_status',
                'slug' => 'compromised',
                'name' => 'Compromised',
                'description' => 'Key is believed to have been compromised',
                'numeric_value' => null,
                'metadata' => ['is_usable' => false],
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function certificateTypes(): array
    {
        return [
            [
                'type' => 'certificate_type',
                'slug' => 'root_ca',
                'name' => 'Root CA',
                'description' => 'Root Certificate Authority',
                'numeric_value' => null,
                'metadata' => ['is_ca' => true],
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'intermediate_ca',
                'name' => 'Intermediate CA',
                'description' => 'Intermediate Certificate Authority',
                'numeric_value' => null,
                'metadata' => ['is_ca' => true],
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'server_tls',
                'name' => 'Server TLS',
                'description' => 'Server TLS/SSL certificate',
                'numeric_value' => null,
                'metadata' => ['is_ca' => false],
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'client_mtls',
                'name' => 'Client mTLS',
                'description' => 'Client mutual TLS certificate',
                'numeric_value' => null,
                'metadata' => ['is_ca' => false],
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'code_signing',
                'name' => 'Code Signing',
                'description' => 'Code signing certificate',
                'numeric_value' => null,
                'metadata' => ['is_ca' => false],
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'smime',
                'name' => 'S/MIME',
                'description' => 'S/MIME email signing and encryption certificate',
                'numeric_value' => null,
                'metadata' => ['is_ca' => false],
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'domain_controller',
                'name' => 'Domain Controller',
                'description' => 'Domain controller authentication certificate',
                'numeric_value' => null,
                'metadata' => ['is_ca' => false],
                'sort_order' => 7,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'user',
                'name' => 'User',
                'description' => 'User authentication certificate',
                'numeric_value' => null,
                'metadata' => ['is_ca' => false],
                'sort_order' => 8,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'computer',
                'name' => 'Computer',
                'description' => 'Computer authentication certificate',
                'numeric_value' => null,
                'metadata' => ['is_ca' => false],
                'sort_order' => 9,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_type',
                'slug' => 'custom',
                'name' => 'Custom',
                'description' => 'Custom certificate type',
                'numeric_value' => null,
                'metadata' => ['is_ca' => false],
                'sort_order' => 10,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function certificateStatuses(): array
    {
        return [
            [
                'type' => 'certificate_status',
                'slug' => 'active',
                'name' => 'Active',
                'description' => 'Certificate is active and valid',
                'numeric_value' => null,
                'metadata' => ['is_valid' => true],
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_status',
                'slug' => 'revoked',
                'name' => 'Revoked',
                'description' => 'Certificate has been revoked',
                'numeric_value' => null,
                'metadata' => ['is_valid' => false],
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_status',
                'slug' => 'expired',
                'name' => 'Expired',
                'description' => 'Certificate has expired',
                'numeric_value' => null,
                'metadata' => ['is_valid' => false],
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_status',
                'slug' => 'suspended',
                'name' => 'Suspended',
                'description' => 'Certificate is temporarily suspended',
                'numeric_value' => null,
                'metadata' => ['is_valid' => false],
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'certificate_status',
                'slug' => 'on_hold',
                'name' => 'On Hold',
                'description' => 'Certificate is on hold',
                'numeric_value' => null,
                'metadata' => ['is_valid' => false],
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function revocationReasons(): array
    {
        return [
            [
                'type' => 'revocation_reason',
                'slug' => 'unspecified',
                'name' => 'Unspecified',
                'description' => 'No specific reason given',
                'numeric_value' => 0,
                'metadata' => [],
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'key_compromise',
                'name' => 'Key Compromise',
                'description' => 'The private key has been compromised',
                'numeric_value' => 1,
                'metadata' => [],
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'ca_compromise',
                'name' => 'CA Compromise',
                'description' => 'The Certificate Authority has been compromised',
                'numeric_value' => 2,
                'metadata' => [],
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'affiliation_changed',
                'name' => 'Affiliation Changed',
                'description' => 'The subject affiliation has changed',
                'numeric_value' => 3,
                'metadata' => [],
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'superseded',
                'name' => 'Superseded',
                'description' => 'The certificate has been superseded by a new one',
                'numeric_value' => 4,
                'metadata' => [],
                'sort_order' => 5,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'cessation_of_operation',
                'name' => 'Cessation of Operation',
                'description' => 'The entity has ceased operations',
                'numeric_value' => 5,
                'metadata' => [],
                'sort_order' => 6,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'certificate_hold',
                'name' => 'Certificate Hold',
                'description' => 'The certificate is temporarily on hold',
                'numeric_value' => 6,
                'metadata' => [],
                'sort_order' => 7,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'remove_from_crl',
                'name' => 'Remove from CRL',
                'description' => 'Remove the certificate from the CRL',
                'numeric_value' => 8,
                'metadata' => [],
                'sort_order' => 8,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'privilege_withdrawn',
                'name' => 'Privilege Withdrawn',
                'description' => 'Privileges granted to the subject have been withdrawn',
                'numeric_value' => 9,
                'metadata' => [],
                'sort_order' => 9,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'revocation_reason',
                'slug' => 'aa_compromise',
                'name' => 'AA Compromise',
                'description' => 'The Attribute Authority has been compromised',
                'numeric_value' => 10,
                'metadata' => [],
                'sort_order' => 10,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function hashAlgorithms(): array
    {
        return [
            [
                'type' => 'hash_algorithm',
                'slug' => 'sha256',
                'name' => 'SHA-256',
                'description' => 'SHA-2 with 256-bit digest',
                'numeric_value' => null,
                'metadata' => ['oid' => '2.16.840.1.101.3.4.2.1'],
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'hash_algorithm',
                'slug' => 'sha384',
                'name' => 'SHA-384',
                'description' => 'SHA-2 with 384-bit digest',
                'numeric_value' => null,
                'metadata' => ['oid' => '2.16.840.1.101.3.4.2.2'],
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'hash_algorithm',
                'slug' => 'sha512',
                'name' => 'SHA-512',
                'description' => 'SHA-2 with 512-bit digest',
                'numeric_value' => null,
                'metadata' => ['oid' => '2.16.840.1.101.3.4.2.3'],
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function exportFormats(): array
    {
        return [
            [
                'type' => 'export_format',
                'slug' => 'pem',
                'name' => 'PEM',
                'description' => 'Privacy-Enhanced Mail format (Base64 encoded)',
                'numeric_value' => null,
                'metadata' => ['mime_type' => 'application/x-pem-file', 'extension' => 'pem'],
                'sort_order' => 1,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'export_format',
                'slug' => 'der',
                'name' => 'DER',
                'description' => 'Distinguished Encoding Rules format (binary)',
                'numeric_value' => null,
                'metadata' => ['mime_type' => 'application/x-x509-ca-cert', 'extension' => 'der'],
                'sort_order' => 2,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'export_format',
                'slug' => 'pkcs12',
                'name' => 'PKCS#12',
                'description' => 'PKCS#12 archive format with private key',
                'numeric_value' => null,
                'metadata' => ['mime_type' => 'application/x-pkcs12', 'extension' => 'p12'],
                'sort_order' => 3,
                'is_active' => true,
                'is_system' => true,
            ],
            [
                'type' => 'export_format',
                'slug' => 'pkcs7',
                'name' => 'PKCS#7',
                'description' => 'PKCS#7 certificate chain format',
                'numeric_value' => null,
                'metadata' => ['mime_type' => 'application/pkcs7-mime', 'extension' => 'p7b'],
                'sort_order' => 4,
                'is_active' => true,
                'is_system' => true,
            ],
        ];
    }

    private function csrStatuses(): array
    {
        return [
            ['type' => 'csr_status', 'slug' => 'pending', 'name' => 'Pending', 'description' => 'CSR is pending review', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'csr_status', 'slug' => 'approved', 'name' => 'Approved', 'description' => 'CSR has been approved', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'csr_status', 'slug' => 'rejected', 'name' => 'Rejected', 'description' => 'CSR has been rejected', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
            ['type' => 'csr_status', 'slug' => 'signed', 'name' => 'Signed', 'description' => 'CSR has been signed into a certificate', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 4, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function scepMessageTypes(): array
    {
        return [
            ['type' => 'scep_message_type', 'slug' => 'pkcs_req', 'name' => 'PKCSReq', 'description' => 'PKCS#10 certificate request', 'numeric_value' => 19, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_message_type', 'slug' => 'cert_rep', 'name' => 'CertRep', 'description' => 'Certificate response', 'numeric_value' => 3, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_message_type', 'slug' => 'get_cert_initial', 'name' => 'GetCertInitial', 'description' => 'Get certificate initial (polling)', 'numeric_value' => 20, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_message_type', 'slug' => 'get_cert', 'name' => 'GetCert', 'description' => 'Get certificate', 'numeric_value' => 21, 'metadata' => [], 'sort_order' => 4, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_message_type', 'slug' => 'get_crl', 'name' => 'GetCRL', 'description' => 'Get certificate revocation list', 'numeric_value' => 22, 'metadata' => [], 'sort_order' => 5, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function scepPkiStatuses(): array
    {
        return [
            ['type' => 'scep_pki_status', 'slug' => 'success', 'name' => 'Success', 'description' => 'SCEP operation completed successfully', 'numeric_value' => 0, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_pki_status', 'slug' => 'failure', 'name' => 'Failure', 'description' => 'SCEP operation failed', 'numeric_value' => 2, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_pki_status', 'slug' => 'pending', 'name' => 'Pending', 'description' => 'SCEP operation is pending', 'numeric_value' => 3, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function scepFailInfos(): array
    {
        return [
            ['type' => 'scep_fail_info', 'slug' => 'bad_alg', 'name' => 'Bad Algorithm', 'description' => 'Unrecognized or unsupported algorithm', 'numeric_value' => 0, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_fail_info', 'slug' => 'bad_message_check', 'name' => 'Bad Message Check', 'description' => 'Integrity check failed', 'numeric_value' => 1, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_fail_info', 'slug' => 'bad_request', 'name' => 'Bad Request', 'description' => 'Transaction not permitted or supported', 'numeric_value' => 2, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_fail_info', 'slug' => 'bad_time', 'name' => 'Bad Time', 'description' => 'Message time field not sufficiently close to system time', 'numeric_value' => 3, 'metadata' => [], 'sort_order' => 4, 'is_active' => true, 'is_system' => true],
            ['type' => 'scep_fail_info', 'slug' => 'bad_cert_id', 'name' => 'Bad Certificate ID', 'description' => 'No certificate could be identified matching the provided criteria', 'numeric_value' => 4, 'metadata' => [], 'sort_order' => 5, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function acmeOrderStatuses(): array
    {
        return [
            ['type' => 'acme_order_status', 'slug' => 'pending', 'name' => 'Pending', 'description' => 'Order is pending authorization', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_order_status', 'slug' => 'ready', 'name' => 'Ready', 'description' => 'Order is ready for finalization', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_order_status', 'slug' => 'processing', 'name' => 'Processing', 'description' => 'Order is being processed', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_order_status', 'slug' => 'valid', 'name' => 'Valid', 'description' => 'Order is valid and certificate is available', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 4, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_order_status', 'slug' => 'invalid', 'name' => 'Invalid', 'description' => 'Order has become invalid', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 5, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function acmeChallengeStatuses(): array
    {
        return [
            ['type' => 'acme_challenge_status', 'slug' => 'pending', 'name' => 'Pending', 'description' => 'Challenge is pending', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_challenge_status', 'slug' => 'processing', 'name' => 'Processing', 'description' => 'Challenge is being validated', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_challenge_status', 'slug' => 'valid', 'name' => 'Valid', 'description' => 'Challenge has been validated', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_challenge_status', 'slug' => 'invalid', 'name' => 'Invalid', 'description' => 'Challenge validation failed', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 4, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function acmeChallengeTypes(): array
    {
        return [
            ['type' => 'acme_challenge_type', 'slug' => 'http-01', 'name' => 'HTTP-01', 'description' => 'HTTP challenge validation', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_challenge_type', 'slug' => 'dns-01', 'name' => 'DNS-01', 'description' => 'DNS challenge validation', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_challenge_type', 'slug' => 'tls-alpn-01', 'name' => 'TLS-ALPN-01', 'description' => 'TLS ALPN challenge validation', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function acmeAuthorizationStatuses(): array
    {
        return [
            ['type' => 'acme_authorization_status', 'slug' => 'pending', 'name' => 'Pending', 'description' => 'Authorization is pending', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_authorization_status', 'slug' => 'valid', 'name' => 'Valid', 'description' => 'Authorization is valid', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_authorization_status', 'slug' => 'invalid', 'name' => 'Invalid', 'description' => 'Authorization is invalid', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_authorization_status', 'slug' => 'deactivated', 'name' => 'Deactivated', 'description' => 'Authorization has been deactivated', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 4, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_authorization_status', 'slug' => 'expired', 'name' => 'Expired', 'description' => 'Authorization has expired', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 5, 'is_active' => true, 'is_system' => true],
            ['type' => 'acme_authorization_status', 'slug' => 'revoked', 'name' => 'Revoked', 'description' => 'Authorization has been revoked', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 6, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function policySeverities(): array
    {
        return [
            ['type' => 'policy_severity', 'slug' => 'error', 'name' => 'Error', 'description' => 'Policy violation that blocks issuance', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'policy_severity', 'slug' => 'warning', 'name' => 'Warning', 'description' => 'Policy warning that should be reviewed', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'policy_severity', 'slug' => 'info', 'name' => 'Info', 'description' => 'Informational policy notice', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function policyActions(): array
    {
        return [
            ['type' => 'policy_action', 'slug' => 'deny', 'name' => 'Deny', 'description' => 'Deny the request', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'policy_action', 'slug' => 'allow', 'name' => 'Allow', 'description' => 'Allow the request', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'policy_action', 'slug' => 'warn', 'name' => 'Warn', 'description' => 'Allow with a warning', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
            ['type' => 'policy_action', 'slug' => 'require_approval', 'name' => 'Require Approval', 'description' => 'Require manual approval before proceeding', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 4, 'is_active' => true, 'is_system' => true],
        ];
    }

    private function nameTypes(): array
    {
        return [
            ['type' => 'name_type', 'slug' => 'dns', 'name' => 'DNS', 'description' => 'DNS domain name', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 1, 'is_active' => true, 'is_system' => true],
            ['type' => 'name_type', 'slug' => 'email', 'name' => 'Email', 'description' => 'RFC 822 email address', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 2, 'is_active' => true, 'is_system' => true],
            ['type' => 'name_type', 'slug' => 'ip', 'name' => 'IP Address', 'description' => 'IP address (IPv4 or IPv6)', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 3, 'is_active' => true, 'is_system' => true],
            ['type' => 'name_type', 'slug' => 'uri', 'name' => 'URI', 'description' => 'Uniform Resource Identifier', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 4, 'is_active' => true, 'is_system' => true],
            ['type' => 'name_type', 'slug' => 'directory', 'name' => 'Directory Name', 'description' => 'X.500 directory name', 'numeric_value' => null, 'metadata' => [], 'sort_order' => 5, 'is_active' => true, 'is_system' => true],
        ];
    }
}
