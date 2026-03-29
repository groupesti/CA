<?php

declare(strict_types=1);

namespace CA\Oid\Registry;

final class MicrosoftOids
{
    // =========================================================================
    // Certificate Template
    // =========================================================================
    public const OID_MS_CERTIFICATE_TEMPLATE = '1.3.6.1.4.1.311.21.7';
    public const OID_MS_CERTIFICATE_TEMPLATE_NAME = '1.3.6.1.4.1.311.20.2';
    public const OID_MS_APPLICATION_POLICIES = '1.3.6.1.4.1.311.21.10';
    /** @deprecated Use OID_MS_CERTIFICATE_TEMPLATE_NAME instead (same OID, v1 template name). */
    public const OID_MS_ENROLL_CERT_TYPE = self::OID_MS_CERTIFICATE_TEMPLATE_NAME;

    // =========================================================================
    // Microsoft Extended Key Usage
    // =========================================================================
    public const OID_MS_SMARTCARD_LOGON = '1.3.6.1.4.1.311.20.2.2';
    public const OID_MS_EFS = '1.3.6.1.4.1.311.10.3.4';
    public const OID_MS_EFS_RECOVERY = '1.3.6.1.4.1.311.10.3.4.1';
    public const OID_MS_DOCUMENT_SIGNING = '1.3.6.1.4.1.311.10.3.12';
    public const OID_MS_KEY_RECOVERY_AGENT = '1.3.6.1.4.1.311.21.6';
    public const OID_MS_LIFETIME_SIGNING = '1.3.6.1.4.1.311.10.3.13';
    public const OID_MS_CTL_USAGE_SIGNING = '1.3.6.1.4.1.311.10.3.1';
    public const OID_MS_NTDS_REPLICATION = '1.3.6.1.4.1.311.25.1';
    public const OID_MS_QUALIFIED_SUBORDINATION = '1.3.6.1.4.1.311.10.3.10';
    public const OID_MS_ROOT_LIST_SIGNER = '1.3.6.1.4.1.311.10.3.9';
    public const OID_MS_KERNEL_MODE_CODE_SIGNING = '1.3.6.1.4.1.311.61.1.1';
    public const OID_MS_WHQL_CRYPTO = '1.3.6.1.4.1.311.10.3.5';
    public const OID_MS_EMBEDDED_NT_CRYPTO = '1.3.6.1.4.1.311.10.3.8';
    public const OID_MS_FILE_RECOVERY = '1.3.6.1.4.1.311.10.3.11';

    // =========================================================================
    // Microsoft Certificate Types / Enrollment
    // =========================================================================
    /** @deprecated Use OID_MS_SMARTCARD_LOGON instead (same OID covers Domain Controller authentication). */
    public const OID_MS_DOMAIN_CONTROLLER = self::OID_MS_SMARTCARD_LOGON;
    public const OID_MS_ENROLLMENT_AGENT = '1.3.6.1.4.1.311.20.2.1';
    public const OID_MS_AUTO_ENROLL_CTL_USAGE = '1.3.6.1.4.1.311.20.1';
    public const OID_MS_REQUEST_CLIENT_INFO = '1.3.6.1.4.1.311.21.20';
    public const OID_MS_ENROLLMENT_NAME_VALUE_PAIR = '1.3.6.1.4.1.311.13.2.1';
    public const OID_MS_ENROLLMENT_CSP_PROVIDER = '1.3.6.1.4.1.311.13.2.2';

    // =========================================================================
    // Microsoft CA / PKI Infrastructure
    // =========================================================================
    public const OID_MS_CA_VERSION = '1.3.6.1.4.1.311.21.1';
    public const OID_MS_PREVIOUS_CA_CERT_HASH = '1.3.6.1.4.1.311.21.2';
    public const OID_MS_CRL_VIRTUAL_BASE = '1.3.6.1.4.1.311.21.3';
    public const OID_MS_CRL_NEXT_PUBLISH = '1.3.6.1.4.1.311.21.4';
    public const OID_MS_CERT_MANIFOLD = '1.3.6.1.4.1.311.21.14';
    public const OID_MS_CROSS_CERT_DIST_POINTS = '1.3.6.1.4.1.311.10.9.1';

    // =========================================================================
    // Microsoft SAN / Subject Attributes
    // =========================================================================
    public const OID_MS_UPN = '1.3.6.1.4.1.311.20.2.3';
    public const OID_MS_NTDS_CA_SECURITY = '1.3.6.1.4.1.311.25.2';
    public const OID_MS_NTDS_OBJECT_SID = '1.3.6.1.4.1.311.25.2.1';

    // =========================================================================
    // Kerberos / Active Directory Authentication
    // =========================================================================
    public const OID_KDC_AUTHENTICATION = '1.3.6.1.5.2.3.5';
    public const OID_PKINIT_KP_CLIENT_AUTH = '1.3.6.1.5.2.3.4';

    /**
     * Return all Microsoft OIDs for seeding.
     *
     * @return array<string, array{name: string, short_name: ?string, description: string, category: string}>
     */
    public static function getAll(): array
    {
        return [
            // Certificate Template
            self::OID_MS_CERTIFICATE_TEMPLATE => [
                'name' => 'msCertificateTemplate',
                'short_name' => null,
                'description' => 'Microsoft Certificate Template Information (v2)',
                'category' => 'microsoft',
            ],
            self::OID_MS_CERTIFICATE_TEMPLATE_NAME => [
                'name' => 'msCertificateTemplateName',
                'short_name' => null,
                'description' => 'Microsoft Certificate Template Name (szOID_ENROLL_CERTTYPE)',
                'category' => 'microsoft',
            ],
            self::OID_MS_APPLICATION_POLICIES => [
                'name' => 'msApplicationPolicies',
                'short_name' => null,
                'description' => 'Microsoft Application Policies Extension',
                'category' => 'microsoft',
            ],

            // Microsoft EKU
            self::OID_MS_SMARTCARD_LOGON => [
                'name' => 'msSmartcardLogon',
                'short_name' => null,
                'description' => 'Microsoft Smart Card Logon',
                'category' => 'microsoft',
            ],
            self::OID_MS_EFS => [
                'name' => 'msEFS',
                'short_name' => 'EFS',
                'description' => 'Microsoft Encrypting File System',
                'category' => 'microsoft',
            ],
            self::OID_MS_EFS_RECOVERY => [
                'name' => 'msEFSRecovery',
                'short_name' => null,
                'description' => 'Microsoft EFS Recovery',
                'category' => 'microsoft',
            ],
            self::OID_MS_DOCUMENT_SIGNING => [
                'name' => 'msDocumentSigning',
                'short_name' => null,
                'description' => 'Microsoft Document Signing',
                'category' => 'microsoft',
            ],
            self::OID_MS_KEY_RECOVERY_AGENT => [
                'name' => 'msKeyRecoveryAgent',
                'short_name' => 'KRA',
                'description' => 'Microsoft Key Recovery Agent',
                'category' => 'microsoft',
            ],
            self::OID_MS_LIFETIME_SIGNING => [
                'name' => 'msLifetimeSigning',
                'short_name' => null,
                'description' => 'Microsoft Lifetime Signing',
                'category' => 'microsoft',
            ],
            self::OID_MS_CTL_USAGE_SIGNING => [
                'name' => 'msCTLUsageSigning',
                'short_name' => null,
                'description' => 'Microsoft Certificate Trust List (CTL) Usage Signing',
                'category' => 'microsoft',
            ],
            self::OID_MS_NTDS_REPLICATION => [
                'name' => 'msNTDSReplication',
                'short_name' => null,
                'description' => 'Microsoft NTDS Active Directory Replication',
                'category' => 'microsoft',
            ],
            self::OID_MS_QUALIFIED_SUBORDINATION => [
                'name' => 'msQualifiedSubordination',
                'short_name' => null,
                'description' => 'Microsoft Qualified Subordination',
                'category' => 'microsoft',
            ],
            self::OID_MS_ROOT_LIST_SIGNER => [
                'name' => 'msRootListSigner',
                'short_name' => null,
                'description' => 'Microsoft Root List Signer',
                'category' => 'microsoft',
            ],
            self::OID_MS_KERNEL_MODE_CODE_SIGNING => [
                'name' => 'msKernelModeCodeSigning',
                'short_name' => null,
                'description' => 'Microsoft Kernel Mode Code Signing',
                'category' => 'microsoft',
            ],
            self::OID_MS_WHQL_CRYPTO => [
                'name' => 'msWHQLCrypto',
                'short_name' => 'WHQL',
                'description' => 'Microsoft Windows Hardware Quality Labs Crypto',
                'category' => 'microsoft',
            ],
            self::OID_MS_EMBEDDED_NT_CRYPTO => [
                'name' => 'msEmbeddedNTCrypto',
                'short_name' => null,
                'description' => 'Microsoft Embedded NT Crypto',
                'category' => 'microsoft',
            ],

            // Microsoft Certificate Types / Enrollment
            self::OID_MS_ENROLLMENT_AGENT => [
                'name' => 'msEnrollmentAgent',
                'short_name' => null,
                'description' => 'Microsoft Enrollment Agent',
                'category' => 'microsoft',
            ],
            self::OID_MS_AUTO_ENROLL_CTL_USAGE => [
                'name' => 'msAutoEnrollCTLUsage',
                'short_name' => null,
                'description' => 'Microsoft Auto-Enrollment CTL Usage',
                'category' => 'microsoft',
            ],
            self::OID_MS_REQUEST_CLIENT_INFO => [
                'name' => 'msRequestClientInfo',
                'short_name' => null,
                'description' => 'Microsoft Request Client Information',
                'category' => 'microsoft',
            ],
            self::OID_MS_ENROLLMENT_NAME_VALUE_PAIR => [
                'name' => 'msEnrollmentNameValuePair',
                'short_name' => null,
                'description' => 'Microsoft Enrollment Name Value Pair',
                'category' => 'microsoft',
            ],
            self::OID_MS_ENROLLMENT_CSP_PROVIDER => [
                'name' => 'msEnrollmentCSPProvider',
                'short_name' => null,
                'description' => 'Microsoft Enrollment CSP Provider',
                'category' => 'microsoft',
            ],

            // Microsoft CA / PKI
            self::OID_MS_CA_VERSION => [
                'name' => 'msCAVersion',
                'short_name' => null,
                'description' => 'Microsoft CA Version',
                'category' => 'microsoft',
            ],
            self::OID_MS_PREVIOUS_CA_CERT_HASH => [
                'name' => 'msPreviousCACertHash',
                'short_name' => null,
                'description' => 'Microsoft Previous CA Certificate Hash',
                'category' => 'microsoft',
            ],
            self::OID_MS_CRL_VIRTUAL_BASE => [
                'name' => 'msCRLVirtualBase',
                'short_name' => null,
                'description' => 'Microsoft CRL Virtual Base',
                'category' => 'microsoft',
            ],
            self::OID_MS_CRL_NEXT_PUBLISH => [
                'name' => 'msCRLNextPublish',
                'short_name' => null,
                'description' => 'Microsoft CRL Next Publish',
                'category' => 'microsoft',
            ],
            self::OID_MS_CERT_MANIFOLD => [
                'name' => 'msCertManifold',
                'short_name' => null,
                'description' => 'Microsoft Certificate Manifold',
                'category' => 'microsoft',
            ],
            self::OID_MS_CROSS_CERT_DIST_POINTS => [
                'name' => 'msCrossCertDistPoints',
                'short_name' => null,
                'description' => 'Microsoft Cross-Certificate Distribution Points',
                'category' => 'microsoft',
            ],

            // Microsoft SAN / Subject Attributes
            self::OID_MS_UPN => [
                'name' => 'msUPN',
                'short_name' => 'UPN',
                'description' => 'Microsoft User Principal Name (SAN)',
                'category' => 'microsoft',
            ],
            self::OID_MS_NTDS_CA_SECURITY => [
                'name' => 'msNTDSCASecurity',
                'short_name' => null,
                'description' => 'Microsoft NTDS CA Security Extension',
                'category' => 'microsoft',
            ],
            self::OID_MS_NTDS_OBJECT_SID => [
                'name' => 'msNTDSObjectSid',
                'short_name' => null,
                'description' => 'Microsoft NTDS Object SID',
                'category' => 'microsoft',
            ],

            // Kerberos / AD Authentication
            self::OID_KDC_AUTHENTICATION => [
                'name' => 'kdcAuthentication',
                'short_name' => 'KDC',
                'description' => 'Kerberos KDC Authentication',
                'category' => 'microsoft',
            ],
            self::OID_PKINIT_KP_CLIENT_AUTH => [
                'name' => 'pkinitKPClientAuth',
                'short_name' => null,
                'description' => 'PKINIT Client Authentication',
                'category' => 'microsoft',
            ],
        ];
    }
}
