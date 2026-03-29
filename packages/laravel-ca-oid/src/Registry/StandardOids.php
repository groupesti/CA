<?php

declare(strict_types=1);

namespace CA\Oid\Registry;

final class StandardOids
{
    // =========================================================================
    // Distinguished Name Attributes
    // =========================================================================
    public const OID_COMMON_NAME = '2.5.4.3';
    public const OID_SURNAME = '2.5.4.4';
    public const OID_SERIAL_NUMBER = '2.5.4.5';
    public const OID_COUNTRY = '2.5.4.6';
    public const OID_LOCALITY = '2.5.4.7';
    public const OID_STATE = '2.5.4.8';
    public const OID_STREET_ADDRESS = '2.5.4.9';
    public const OID_ORGANIZATION = '2.5.4.10';
    public const OID_ORGANIZATIONAL_UNIT = '2.5.4.11';
    public const OID_TITLE = '2.5.4.12';
    public const OID_DESCRIPTION = '2.5.4.13';
    public const OID_POSTAL_CODE = '2.5.4.17';
    public const OID_GIVEN_NAME = '2.5.4.42';
    public const OID_INITIALS = '2.5.4.43';
    public const OID_DN_QUALIFIER = '2.5.4.46';
    public const OID_EMAIL = '1.2.840.113549.1.9.1';
    public const OID_DOMAIN_COMPONENT = '0.9.2342.19200300.100.1.25';
    public const OID_USER_ID = '0.9.2342.19200300.100.1.1';

    // =========================================================================
    // X.509 Extensions
    // =========================================================================
    public const OID_SUBJECT_KEY_IDENTIFIER = '2.5.29.14';
    public const OID_KEY_USAGE = '2.5.29.15';
    public const OID_SUBJECT_ALT_NAME = '2.5.29.17';
    public const OID_ISSUER_ALT_NAME = '2.5.29.18';
    public const OID_BASIC_CONSTRAINTS = '2.5.29.19';
    public const OID_CRL_NUMBER = '2.5.29.20';
    public const OID_CRL_REASON = '2.5.29.21';
    public const OID_DELTA_CRL_INDICATOR = '2.5.29.27';
    public const OID_ISSUING_DISTRIBUTION_POINT = '2.5.29.28';
    public const OID_NAME_CONSTRAINTS = '2.5.29.30';
    public const OID_CRL_DISTRIBUTION_POINTS = '2.5.29.31';
    public const OID_CERTIFICATE_POLICIES = '2.5.29.32';
    public const OID_ANY_POLICY = '2.5.29.32.0';
    public const OID_POLICY_MAPPINGS = '2.5.29.33';
    public const OID_AUTHORITY_KEY_IDENTIFIER = '2.5.29.35';
    public const OID_POLICY_CONSTRAINTS = '2.5.29.36';
    public const OID_EXTENDED_KEY_USAGE = '2.5.29.37';
    public const OID_FRESHEST_CRL = '2.5.29.46';
    public const OID_INHIBIT_ANY_POLICY = '2.5.29.54';

    // =========================================================================
    // Extended Key Usage Values
    // =========================================================================
    public const OID_SERVER_AUTH = '1.3.6.1.5.5.7.3.1';
    public const OID_CLIENT_AUTH = '1.3.6.1.5.5.7.3.2';
    public const OID_CODE_SIGNING = '1.3.6.1.5.5.7.3.3';
    public const OID_EMAIL_PROTECTION = '1.3.6.1.5.5.7.3.4';
    public const OID_IPSEC_END_SYSTEM = '1.3.6.1.5.5.7.3.5';
    public const OID_IPSEC_TUNNEL = '1.3.6.1.5.5.7.3.6';
    public const OID_IPSEC_USER = '1.3.6.1.5.5.7.3.7';
    public const OID_TIME_STAMPING = '1.3.6.1.5.5.7.3.8';
    public const OID_OCSP_SIGNING = '1.3.6.1.5.5.7.3.9';
    public const OID_ANY_EXTENDED_KEY_USAGE = '2.5.29.37.0';

    // =========================================================================
    // Authority Information Access
    // =========================================================================
    public const OID_AUTHORITY_INFO_ACCESS = '1.3.6.1.5.5.7.1.1';
    public const OID_SUBJECT_INFO_ACCESS = '1.3.6.1.5.5.7.1.11';
    public const OID_OCSP = '1.3.6.1.5.5.7.48.1';
    public const OID_CA_ISSUERS = '1.3.6.1.5.5.7.48.2';
    public const OID_OCSP_NOCHECK = '1.3.6.1.5.5.7.48.1.5';

    // =========================================================================
    // Certificate Transparency
    // =========================================================================
    public const OID_CT_PRECERTIFICATE_SCTS = '1.3.6.1.4.1.11129.2.4.2';
    public const OID_CT_PRECERTIFICATE_POISON = '1.3.6.1.4.1.11129.2.4.3';
    public const OID_CT_PRECERTIFICATE_SIGNING = '1.3.6.1.4.1.11129.2.4.4';

    // =========================================================================
    // Signature Algorithms
    // =========================================================================
    public const OID_SHA1_WITH_RSA = '1.2.840.113549.1.1.5';
    public const OID_SHA256_WITH_RSA = '1.2.840.113549.1.1.11';
    public const OID_SHA384_WITH_RSA = '1.2.840.113549.1.1.12';
    public const OID_SHA512_WITH_RSA = '1.2.840.113549.1.1.13';
    public const OID_RSA_PSS = '1.2.840.113549.1.1.10';
    public const OID_ECDSA_WITH_SHA256 = '1.2.840.10045.4.3.2';
    public const OID_ECDSA_WITH_SHA384 = '1.2.840.10045.4.3.3';
    public const OID_ECDSA_WITH_SHA512 = '1.2.840.10045.4.3.4';
    public const OID_ED25519 = '1.3.101.112';
    public const OID_ED448 = '1.3.101.113';

    // =========================================================================
    // Key Algorithms
    // =========================================================================
    public const OID_RSA_ENCRYPTION = '1.2.840.113549.1.1.1';
    public const OID_EC_PUBLIC_KEY = '1.2.840.10045.2.1';
    public const OID_DH_KEY_AGREEMENT = '1.2.840.113549.1.3.1';
    public const OID_X25519 = '1.3.101.110';
    public const OID_X448 = '1.3.101.111';

    // =========================================================================
    // Elliptic Curves
    // =========================================================================
    public const OID_SECP256R1 = '1.2.840.10045.3.1.7';
    public const OID_SECP384R1 = '1.3.132.0.34';
    public const OID_SECP521R1 = '1.3.132.0.35';
    public const OID_SECP256K1 = '1.3.132.0.10';

    // =========================================================================
    // Hash Algorithms
    // =========================================================================
    public const OID_MD5 = '1.2.840.113549.2.5';
    public const OID_SHA1 = '1.3.14.3.2.26';
    public const OID_SHA256 = '2.16.840.1.101.3.4.2.1';
    public const OID_SHA384 = '2.16.840.1.101.3.4.2.2';
    public const OID_SHA512 = '2.16.840.1.101.3.4.2.3';
    public const OID_SHA3_256 = '2.16.840.1.101.3.4.2.8';
    public const OID_SHA3_384 = '2.16.840.1.101.3.4.2.9';
    public const OID_SHA3_512 = '2.16.840.1.101.3.4.2.10';

    // =========================================================================
    // PKCS#9 Attributes
    // =========================================================================
    public const OID_CONTENT_TYPE = '1.2.840.113549.1.9.3';
    public const OID_MESSAGE_DIGEST = '1.2.840.113549.1.9.4';
    public const OID_SIGNING_TIME = '1.2.840.113549.1.9.5';
    public const OID_COUNTER_SIGNATURE = '1.2.840.113549.1.9.6';
    public const OID_CHALLENGE_PASSWORD = '1.2.840.113549.1.9.7';
    public const OID_UNSTRUCTURED_ADDRESS = '1.2.840.113549.1.9.8';
    public const OID_EXTENSION_REQUEST = '1.2.840.113549.1.9.14';
    public const OID_SMIME_CAPABILITIES = '1.2.840.113549.1.9.15';
    public const OID_PKCS12_FRIENDLY_NAME = '1.2.840.113549.1.9.20';
    public const OID_PKCS12_LOCAL_KEY_ID = '1.2.840.113549.1.9.21';

    // =========================================================================
    // PKCS#7 / CMS
    // =========================================================================
    public const OID_PKCS7_DATA = '1.2.840.113549.1.7.1';
    public const OID_PKCS7_SIGNED_DATA = '1.2.840.113549.1.7.2';
    public const OID_PKCS7_ENVELOPED_DATA = '1.2.840.113549.1.7.3';
    public const OID_PKCS7_SIGNED_AND_ENVELOPED_DATA = '1.2.840.113549.1.7.4';
    public const OID_PKCS7_DIGESTED_DATA = '1.2.840.113549.1.7.5';
    public const OID_PKCS7_ENCRYPTED_DATA = '1.2.840.113549.1.7.6';

    // =========================================================================
    // PKCS#12
    // =========================================================================
    public const OID_PKCS12_KEY_BAG = '1.2.840.113549.1.12.10.1.1';
    public const OID_PKCS12_SHROUDED_KEY_BAG = '1.2.840.113549.1.12.10.1.2';
    public const OID_PKCS12_CERT_BAG = '1.2.840.113549.1.12.10.1.3';
    public const OID_PKCS12_CRL_BAG = '1.2.840.113549.1.12.10.1.4';
    public const OID_PKCS12_SECRET_BAG = '1.2.840.113549.1.12.10.1.5';
    public const OID_PKCS12_SAFE_CONTENTS_BAG = '1.2.840.113549.1.12.10.1.6';

    // =========================================================================
    // PKCS#12 PBE Algorithms
    // =========================================================================
    public const OID_PBE_SHA1_RC4_128 = '1.2.840.113549.1.12.1.1';
    public const OID_PBE_SHA1_3DES = '1.2.840.113549.1.12.1.3';
    public const OID_PBES2 = '1.2.840.113549.1.5.13';
    public const OID_PBKDF2 = '1.2.840.113549.1.5.12';

    // =========================================================================
    // AES Encryption
    // =========================================================================
    public const OID_AES_128_CBC = '2.16.840.1.101.3.4.1.2';
    public const OID_AES_192_CBC = '2.16.840.1.101.3.4.1.22';
    public const OID_AES_256_CBC = '2.16.840.1.101.3.4.1.42';

    /**
     * Return all standard OIDs for seeding.
     *
     * @return array<string, array{name: string, description: string, category: string}>
     */
    public static function getAll(): array
    {
        return [
            // Distinguished Name Attributes
            self::OID_COMMON_NAME => [
                'name' => 'commonName',
                'short_name' => 'CN',
                'description' => 'X.520 Common Name',
                'category' => 'x509',
            ],
            self::OID_SURNAME => [
                'name' => 'surname',
                'short_name' => 'SN',
                'description' => 'X.520 Surname',
                'category' => 'x509',
            ],
            self::OID_SERIAL_NUMBER => [
                'name' => 'serialNumber',
                'short_name' => null,
                'description' => 'X.520 Serial Number',
                'category' => 'x509',
            ],
            self::OID_COUNTRY => [
                'name' => 'countryName',
                'short_name' => 'C',
                'description' => 'X.520 Country Name',
                'category' => 'x509',
            ],
            self::OID_LOCALITY => [
                'name' => 'localityName',
                'short_name' => 'L',
                'description' => 'X.520 Locality Name',
                'category' => 'x509',
            ],
            self::OID_STATE => [
                'name' => 'stateOrProvinceName',
                'short_name' => 'ST',
                'description' => 'X.520 State or Province Name',
                'category' => 'x509',
            ],
            self::OID_STREET_ADDRESS => [
                'name' => 'streetAddress',
                'short_name' => 'STREET',
                'description' => 'X.520 Street Address',
                'category' => 'x509',
            ],
            self::OID_ORGANIZATION => [
                'name' => 'organizationName',
                'short_name' => 'O',
                'description' => 'X.520 Organization Name',
                'category' => 'x509',
            ],
            self::OID_ORGANIZATIONAL_UNIT => [
                'name' => 'organizationalUnitName',
                'short_name' => 'OU',
                'description' => 'X.520 Organizational Unit Name',
                'category' => 'x509',
            ],
            self::OID_TITLE => [
                'name' => 'title',
                'short_name' => null,
                'description' => 'X.520 Title',
                'category' => 'x509',
            ],
            self::OID_DESCRIPTION => [
                'name' => 'description',
                'short_name' => null,
                'description' => 'X.520 Description',
                'category' => 'x509',
            ],
            self::OID_POSTAL_CODE => [
                'name' => 'postalCode',
                'short_name' => null,
                'description' => 'X.520 Postal Code',
                'category' => 'x509',
            ],
            self::OID_GIVEN_NAME => [
                'name' => 'givenName',
                'short_name' => 'GN',
                'description' => 'X.520 Given Name',
                'category' => 'x509',
            ],
            self::OID_INITIALS => [
                'name' => 'initials',
                'short_name' => null,
                'description' => 'X.520 Initials',
                'category' => 'x509',
            ],
            self::OID_DN_QUALIFIER => [
                'name' => 'dnQualifier',
                'short_name' => null,
                'description' => 'X.520 DN Qualifier',
                'category' => 'x509',
            ],
            self::OID_EMAIL => [
                'name' => 'emailAddress',
                'short_name' => 'E',
                'description' => 'PKCS#9 Email Address',
                'category' => 'x509',
            ],
            self::OID_DOMAIN_COMPONENT => [
                'name' => 'domainComponent',
                'short_name' => 'DC',
                'description' => 'RFC 4519 Domain Component',
                'category' => 'x509',
            ],
            self::OID_USER_ID => [
                'name' => 'userId',
                'short_name' => 'UID',
                'description' => 'RFC 4519 User ID',
                'category' => 'x509',
            ],

            // X.509 Extensions
            self::OID_SUBJECT_KEY_IDENTIFIER => [
                'name' => 'subjectKeyIdentifier',
                'short_name' => 'SKI',
                'description' => 'X.509v3 Subject Key Identifier',
                'category' => 'x509',
            ],
            self::OID_KEY_USAGE => [
                'name' => 'keyUsage',
                'short_name' => 'KU',
                'description' => 'X.509v3 Key Usage',
                'category' => 'key_usage',
            ],
            self::OID_SUBJECT_ALT_NAME => [
                'name' => 'subjectAltName',
                'short_name' => 'SAN',
                'description' => 'X.509v3 Subject Alternative Name',
                'category' => 'x509',
            ],
            self::OID_ISSUER_ALT_NAME => [
                'name' => 'issuerAltName',
                'short_name' => 'IAN',
                'description' => 'X.509v3 Issuer Alternative Name',
                'category' => 'x509',
            ],
            self::OID_BASIC_CONSTRAINTS => [
                'name' => 'basicConstraints',
                'short_name' => 'BC',
                'description' => 'X.509v3 Basic Constraints',
                'category' => 'x509',
            ],
            self::OID_CRL_NUMBER => [
                'name' => 'cRLNumber',
                'short_name' => null,
                'description' => 'X.509v3 CRL Number',
                'category' => 'x509',
            ],
            self::OID_CRL_REASON => [
                'name' => 'cRLReason',
                'short_name' => null,
                'description' => 'X.509v3 CRL Reason Code',
                'category' => 'x509',
            ],
            self::OID_DELTA_CRL_INDICATOR => [
                'name' => 'deltaCRLIndicator',
                'short_name' => null,
                'description' => 'X.509v3 Delta CRL Indicator',
                'category' => 'x509',
            ],
            self::OID_ISSUING_DISTRIBUTION_POINT => [
                'name' => 'issuingDistributionPoint',
                'short_name' => 'IDP',
                'description' => 'X.509v3 Issuing Distribution Point',
                'category' => 'x509',
            ],
            self::OID_NAME_CONSTRAINTS => [
                'name' => 'nameConstraints',
                'short_name' => null,
                'description' => 'X.509v3 Name Constraints',
                'category' => 'x509',
            ],
            self::OID_CRL_DISTRIBUTION_POINTS => [
                'name' => 'cRLDistributionPoints',
                'short_name' => 'CDP',
                'description' => 'X.509v3 CRL Distribution Points',
                'category' => 'x509',
            ],
            self::OID_CERTIFICATE_POLICIES => [
                'name' => 'certificatePolicies',
                'short_name' => null,
                'description' => 'X.509v3 Certificate Policies',
                'category' => 'x509',
            ],
            self::OID_ANY_POLICY => [
                'name' => 'anyPolicy',
                'short_name' => null,
                'description' => 'X.509v3 Any Policy',
                'category' => 'x509',
            ],
            self::OID_POLICY_MAPPINGS => [
                'name' => 'policyMappings',
                'short_name' => null,
                'description' => 'X.509v3 Policy Mappings',
                'category' => 'x509',
            ],
            self::OID_AUTHORITY_KEY_IDENTIFIER => [
                'name' => 'authorityKeyIdentifier',
                'short_name' => 'AKI',
                'description' => 'X.509v3 Authority Key Identifier',
                'category' => 'x509',
            ],
            self::OID_POLICY_CONSTRAINTS => [
                'name' => 'policyConstraints',
                'short_name' => null,
                'description' => 'X.509v3 Policy Constraints',
                'category' => 'x509',
            ],
            self::OID_EXTENDED_KEY_USAGE => [
                'name' => 'extendedKeyUsage',
                'short_name' => 'EKU',
                'description' => 'X.509v3 Extended Key Usage',
                'category' => 'eku',
            ],
            self::OID_FRESHEST_CRL => [
                'name' => 'freshestCRL',
                'short_name' => null,
                'description' => 'X.509v3 Freshest CRL (Delta CRL Distribution Point)',
                'category' => 'x509',
            ],
            self::OID_INHIBIT_ANY_POLICY => [
                'name' => 'inhibitAnyPolicy',
                'short_name' => null,
                'description' => 'X.509v3 Inhibit Any Policy',
                'category' => 'x509',
            ],

            // Extended Key Usage Values
            self::OID_SERVER_AUTH => [
                'name' => 'serverAuth',
                'short_name' => null,
                'description' => 'TLS Web Server Authentication',
                'category' => 'eku',
            ],
            self::OID_CLIENT_AUTH => [
                'name' => 'clientAuth',
                'short_name' => null,
                'description' => 'TLS Web Client Authentication',
                'category' => 'eku',
            ],
            self::OID_CODE_SIGNING => [
                'name' => 'codeSigning',
                'short_name' => null,
                'description' => 'Code Signing',
                'category' => 'eku',
            ],
            self::OID_EMAIL_PROTECTION => [
                'name' => 'emailProtection',
                'short_name' => null,
                'description' => 'E-mail Protection (S/MIME)',
                'category' => 'eku',
            ],
            self::OID_IPSEC_END_SYSTEM => [
                'name' => 'ipsecEndSystem',
                'short_name' => null,
                'description' => 'IPSec End System',
                'category' => 'eku',
            ],
            self::OID_IPSEC_TUNNEL => [
                'name' => 'ipsecTunnel',
                'short_name' => null,
                'description' => 'IPSec Tunnel',
                'category' => 'eku',
            ],
            self::OID_IPSEC_USER => [
                'name' => 'ipsecUser',
                'short_name' => null,
                'description' => 'IPSec User',
                'category' => 'eku',
            ],
            self::OID_TIME_STAMPING => [
                'name' => 'timeStamping',
                'short_name' => null,
                'description' => 'Time Stamping',
                'category' => 'eku',
            ],
            self::OID_OCSP_SIGNING => [
                'name' => 'OCSPSigning',
                'short_name' => null,
                'description' => 'OCSP Signing',
                'category' => 'eku',
            ],
            self::OID_ANY_EXTENDED_KEY_USAGE => [
                'name' => 'anyExtendedKeyUsage',
                'short_name' => null,
                'description' => 'Any Extended Key Usage',
                'category' => 'eku',
            ],

            // Authority Information Access
            self::OID_AUTHORITY_INFO_ACCESS => [
                'name' => 'authorityInfoAccess',
                'short_name' => 'AIA',
                'description' => 'Authority Information Access',
                'category' => 'x509',
            ],
            self::OID_SUBJECT_INFO_ACCESS => [
                'name' => 'subjectInfoAccess',
                'short_name' => 'SIA',
                'description' => 'Subject Information Access',
                'category' => 'x509',
            ],
            self::OID_OCSP => [
                'name' => 'ocsp',
                'short_name' => 'OCSP',
                'description' => 'Online Certificate Status Protocol',
                'category' => 'x509',
            ],
            self::OID_CA_ISSUERS => [
                'name' => 'caIssuers',
                'short_name' => null,
                'description' => 'CA Issuers Access Method',
                'category' => 'x509',
            ],
            self::OID_OCSP_NOCHECK => [
                'name' => 'ocspNoCheck',
                'short_name' => null,
                'description' => 'OCSP No Check Extension',
                'category' => 'x509',
            ],

            // Certificate Transparency
            self::OID_CT_PRECERTIFICATE_SCTS => [
                'name' => 'ctPrecertificateSCTs',
                'short_name' => 'SCT',
                'description' => 'Certificate Transparency Precertificate SCTs',
                'category' => 'x509',
            ],
            self::OID_CT_PRECERTIFICATE_POISON => [
                'name' => 'ctPrecertificatePoison',
                'short_name' => null,
                'description' => 'Certificate Transparency Precertificate Poison',
                'category' => 'x509',
            ],
            self::OID_CT_PRECERTIFICATE_SIGNING => [
                'name' => 'ctPrecertificateSigning',
                'short_name' => null,
                'description' => 'Certificate Transparency Precertificate Signing Certificate',
                'category' => 'x509',
            ],

            // Signature Algorithms
            self::OID_SHA1_WITH_RSA => [
                'name' => 'sha1WithRSAEncryption',
                'short_name' => null,
                'description' => 'SHA-1 with RSA Encryption (deprecated)',
                'category' => 'hash',
            ],
            self::OID_SHA256_WITH_RSA => [
                'name' => 'sha256WithRSAEncryption',
                'short_name' => null,
                'description' => 'SHA-256 with RSA Encryption',
                'category' => 'hash',
            ],
            self::OID_SHA384_WITH_RSA => [
                'name' => 'sha384WithRSAEncryption',
                'short_name' => null,
                'description' => 'SHA-384 with RSA Encryption',
                'category' => 'hash',
            ],
            self::OID_SHA512_WITH_RSA => [
                'name' => 'sha512WithRSAEncryption',
                'short_name' => null,
                'description' => 'SHA-512 with RSA Encryption',
                'category' => 'hash',
            ],
            self::OID_RSA_PSS => [
                'name' => 'rsaPSS',
                'short_name' => null,
                'description' => 'RSA-PSS Signature Algorithm',
                'category' => 'hash',
            ],
            self::OID_ECDSA_WITH_SHA256 => [
                'name' => 'ecdsaWithSHA256',
                'short_name' => null,
                'description' => 'ECDSA with SHA-256',
                'category' => 'hash',
            ],
            self::OID_ECDSA_WITH_SHA384 => [
                'name' => 'ecdsaWithSHA384',
                'short_name' => null,
                'description' => 'ECDSA with SHA-384',
                'category' => 'hash',
            ],
            self::OID_ECDSA_WITH_SHA512 => [
                'name' => 'ecdsaWithSHA512',
                'short_name' => null,
                'description' => 'ECDSA with SHA-512',
                'category' => 'hash',
            ],
            self::OID_ED25519 => [
                'name' => 'ed25519',
                'short_name' => null,
                'description' => 'Ed25519 Signature Algorithm',
                'category' => 'hash',
            ],
            self::OID_ED448 => [
                'name' => 'ed448',
                'short_name' => null,
                'description' => 'Ed448 Signature Algorithm',
                'category' => 'hash',
            ],

            // Key Algorithms
            self::OID_RSA_ENCRYPTION => [
                'name' => 'rsaEncryption',
                'short_name' => 'RSA',
                'description' => 'RSA Encryption',
                'category' => 'key_usage',
            ],
            self::OID_EC_PUBLIC_KEY => [
                'name' => 'ecPublicKey',
                'short_name' => 'EC',
                'description' => 'Elliptic Curve Public Key',
                'category' => 'key_usage',
            ],
            self::OID_DH_KEY_AGREEMENT => [
                'name' => 'dhKeyAgreement',
                'short_name' => 'DH',
                'description' => 'Diffie-Hellman Key Agreement',
                'category' => 'key_usage',
            ],
            self::OID_X25519 => [
                'name' => 'x25519',
                'short_name' => null,
                'description' => 'X25519 Key Agreement',
                'category' => 'key_usage',
            ],
            self::OID_X448 => [
                'name' => 'x448',
                'short_name' => null,
                'description' => 'X448 Key Agreement',
                'category' => 'key_usage',
            ],

            // Elliptic Curves
            self::OID_SECP256R1 => [
                'name' => 'secp256r1',
                'short_name' => 'P-256',
                'description' => 'NIST P-256 / prime256v1 Curve',
                'category' => 'curve',
            ],
            self::OID_SECP384R1 => [
                'name' => 'secp384r1',
                'short_name' => 'P-384',
                'description' => 'NIST P-384 Curve',
                'category' => 'curve',
            ],
            self::OID_SECP521R1 => [
                'name' => 'secp521r1',
                'short_name' => 'P-521',
                'description' => 'NIST P-521 Curve',
                'category' => 'curve',
            ],
            self::OID_SECP256K1 => [
                'name' => 'secp256k1',
                'short_name' => null,
                'description' => 'secp256k1 Curve (Bitcoin)',
                'category' => 'curve',
            ],

            // Hash Algorithms
            self::OID_MD5 => [
                'name' => 'md5',
                'short_name' => 'MD5',
                'description' => 'MD5 Message Digest (deprecated)',
                'category' => 'hash',
            ],
            self::OID_SHA1 => [
                'name' => 'sha1',
                'short_name' => 'SHA-1',
                'description' => 'SHA-1 Message Digest (deprecated)',
                'category' => 'hash',
            ],
            self::OID_SHA256 => [
                'name' => 'sha256',
                'short_name' => 'SHA-256',
                'description' => 'SHA-256 Message Digest',
                'category' => 'hash',
            ],
            self::OID_SHA384 => [
                'name' => 'sha384',
                'short_name' => 'SHA-384',
                'description' => 'SHA-384 Message Digest',
                'category' => 'hash',
            ],
            self::OID_SHA512 => [
                'name' => 'sha512',
                'short_name' => 'SHA-512',
                'description' => 'SHA-512 Message Digest',
                'category' => 'hash',
            ],
            self::OID_SHA3_256 => [
                'name' => 'sha3-256',
                'short_name' => 'SHA3-256',
                'description' => 'SHA-3 256-bit Message Digest',
                'category' => 'hash',
            ],
            self::OID_SHA3_384 => [
                'name' => 'sha3-384',
                'short_name' => 'SHA3-384',
                'description' => 'SHA-3 384-bit Message Digest',
                'category' => 'hash',
            ],
            self::OID_SHA3_512 => [
                'name' => 'sha3-512',
                'short_name' => 'SHA3-512',
                'description' => 'SHA-3 512-bit Message Digest',
                'category' => 'hash',
            ],

            // PKCS#9 Attributes
            self::OID_CONTENT_TYPE => [
                'name' => 'contentType',
                'short_name' => null,
                'description' => 'PKCS#9 Content Type',
                'category' => 'pkcs',
            ],
            self::OID_MESSAGE_DIGEST => [
                'name' => 'messageDigest',
                'short_name' => null,
                'description' => 'PKCS#9 Message Digest',
                'category' => 'pkcs',
            ],
            self::OID_SIGNING_TIME => [
                'name' => 'signingTime',
                'short_name' => null,
                'description' => 'PKCS#9 Signing Time',
                'category' => 'pkcs',
            ],
            self::OID_COUNTER_SIGNATURE => [
                'name' => 'counterSignature',
                'short_name' => null,
                'description' => 'PKCS#9 Counter Signature',
                'category' => 'pkcs',
            ],
            self::OID_CHALLENGE_PASSWORD => [
                'name' => 'challengePassword',
                'short_name' => null,
                'description' => 'PKCS#9 Challenge Password',
                'category' => 'pkcs',
            ],
            self::OID_UNSTRUCTURED_ADDRESS => [
                'name' => 'unstructuredAddress',
                'short_name' => null,
                'description' => 'PKCS#9 Unstructured Address',
                'category' => 'pkcs',
            ],
            self::OID_EXTENSION_REQUEST => [
                'name' => 'extensionRequest',
                'short_name' => null,
                'description' => 'PKCS#9 Extension Request',
                'category' => 'pkcs',
            ],
            self::OID_SMIME_CAPABILITIES => [
                'name' => 'smimeCapabilities',
                'short_name' => null,
                'description' => 'PKCS#9 S/MIME Capabilities',
                'category' => 'pkcs',
            ],
            self::OID_PKCS12_FRIENDLY_NAME => [
                'name' => 'friendlyName',
                'short_name' => null,
                'description' => 'PKCS#12 Friendly Name',
                'category' => 'pkcs',
            ],
            self::OID_PKCS12_LOCAL_KEY_ID => [
                'name' => 'localKeyId',
                'short_name' => null,
                'description' => 'PKCS#12 Local Key ID',
                'category' => 'pkcs',
            ],

            // PKCS#7 / CMS
            self::OID_PKCS7_DATA => [
                'name' => 'data',
                'short_name' => null,
                'description' => 'PKCS#7 Data Content Type',
                'category' => 'pkcs',
            ],
            self::OID_PKCS7_SIGNED_DATA => [
                'name' => 'signedData',
                'short_name' => null,
                'description' => 'PKCS#7 Signed Data Content Type',
                'category' => 'pkcs',
            ],
            self::OID_PKCS7_ENVELOPED_DATA => [
                'name' => 'envelopedData',
                'short_name' => null,
                'description' => 'PKCS#7 Enveloped Data Content Type',
                'category' => 'pkcs',
            ],
            self::OID_PKCS7_SIGNED_AND_ENVELOPED_DATA => [
                'name' => 'signedAndEnvelopedData',
                'short_name' => null,
                'description' => 'PKCS#7 Signed and Enveloped Data Content Type',
                'category' => 'pkcs',
            ],
            self::OID_PKCS7_DIGESTED_DATA => [
                'name' => 'digestedData',
                'short_name' => null,
                'description' => 'PKCS#7 Digested Data Content Type',
                'category' => 'pkcs',
            ],
            self::OID_PKCS7_ENCRYPTED_DATA => [
                'name' => 'encryptedData',
                'short_name' => null,
                'description' => 'PKCS#7 Encrypted Data Content Type',
                'category' => 'pkcs',
            ],

            // PKCS#12
            self::OID_PKCS12_KEY_BAG => [
                'name' => 'keyBag',
                'short_name' => null,
                'description' => 'PKCS#12 Key Bag',
                'category' => 'pkcs',
            ],
            self::OID_PKCS12_SHROUDED_KEY_BAG => [
                'name' => 'pkcs8ShroudedKeyBag',
                'short_name' => null,
                'description' => 'PKCS#12 PKCS#8 Shrouded Key Bag',
                'category' => 'pkcs',
            ],
            self::OID_PKCS12_CERT_BAG => [
                'name' => 'certBag',
                'short_name' => null,
                'description' => 'PKCS#12 Certificate Bag',
                'category' => 'pkcs',
            ],
            self::OID_PKCS12_CRL_BAG => [
                'name' => 'crlBag',
                'short_name' => null,
                'description' => 'PKCS#12 CRL Bag',
                'category' => 'pkcs',
            ],
            self::OID_PKCS12_SECRET_BAG => [
                'name' => 'secretBag',
                'short_name' => null,
                'description' => 'PKCS#12 Secret Bag',
                'category' => 'pkcs',
            ],
            self::OID_PKCS12_SAFE_CONTENTS_BAG => [
                'name' => 'safeContentsBag',
                'short_name' => null,
                'description' => 'PKCS#12 Safe Contents Bag',
                'category' => 'pkcs',
            ],

            // PBE Algorithms
            self::OID_PBE_SHA1_RC4_128 => [
                'name' => 'pbeWithSHA1And128BitRC4',
                'short_name' => null,
                'description' => 'PBE with SHA-1 and 128-bit RC4',
                'category' => 'pkcs',
            ],
            self::OID_PBE_SHA1_3DES => [
                'name' => 'pbeWithSHA1And3KeyTripleDES',
                'short_name' => null,
                'description' => 'PBE with SHA-1 and 3-Key Triple DES-CBC',
                'category' => 'pkcs',
            ],
            self::OID_PBES2 => [
                'name' => 'pbes2',
                'short_name' => 'PBES2',
                'description' => 'PKCS#5 PBES2',
                'category' => 'pkcs',
            ],
            self::OID_PBKDF2 => [
                'name' => 'pbkdf2',
                'short_name' => 'PBKDF2',
                'description' => 'PKCS#5 PBKDF2',
                'category' => 'pkcs',
            ],

            // AES
            self::OID_AES_128_CBC => [
                'name' => 'aes128-CBC',
                'short_name' => 'AES-128-CBC',
                'description' => 'AES 128-bit CBC Encryption',
                'category' => 'hash',
            ],
            self::OID_AES_192_CBC => [
                'name' => 'aes192-CBC',
                'short_name' => 'AES-192-CBC',
                'description' => 'AES 192-bit CBC Encryption',
                'category' => 'hash',
            ],
            self::OID_AES_256_CBC => [
                'name' => 'aes256-CBC',
                'short_name' => 'AES-256-CBC',
                'description' => 'AES 256-bit CBC Encryption',
                'category' => 'hash',
            ],
        ];
    }
}
