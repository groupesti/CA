<?php

declare(strict_types=1);

namespace CA\Oid\Registry;

final class ExtendedValidationOids
{
    // =========================================================================
    // CA/Browser Forum Validation Levels
    // =========================================================================
    public const OID_EV_GUIDELINES = '2.23.140.1.1';
    public const OID_DOMAIN_VALIDATED = '2.23.140.1.2.1';
    public const OID_ORGANIZATION_VALIDATED = '2.23.140.1.2.2';
    public const OID_INDIVIDUAL_VALIDATED = '2.23.140.1.2.3';

    // =========================================================================
    // Code Signing / S/MIME Validation
    // =========================================================================
    public const OID_CODE_SIGNING_REQUIREMENTS = '2.23.140.1.4.1';
    public const OID_SMIME_MAILBOX_VALIDATED = '2.23.140.1.5.1.1';
    public const OID_SMIME_ORGANIZATION_VALIDATED = '2.23.140.1.5.1.2';
    public const OID_SMIME_SPONSOR_VALIDATED = '2.23.140.1.5.1.3';
    public const OID_SMIME_INDIVIDUAL_VALIDATED = '2.23.140.1.5.1.4';

    // =========================================================================
    // Jurisdiction Attributes (EV Certificates)
    // =========================================================================
    public const OID_JURISDICTION_LOCALITY = '1.3.6.1.4.1.311.60.2.1.1';
    public const OID_JURISDICTION_STATE = '1.3.6.1.4.1.311.60.2.1.2';
    public const OID_JURISDICTION_COUNTRY = '1.3.6.1.4.1.311.60.2.1.3';
    public const OID_BUSINESS_CATEGORY = '2.5.4.15';

    // =========================================================================
    // Major CA EV Policy OIDs
    // =========================================================================
    public const OID_DIGICERT_EV = '2.16.840.1.114412.2.1';
    public const OID_GLOBALSIGN_EV = '1.3.6.1.4.1.4146.1.1';
    public const OID_COMODO_EV = '1.3.6.1.4.1.6449.1.2.1.5.1';
    public const OID_ENTRUST_EV = '2.16.840.1.114028.10.1.2';
    public const OID_GODADDY_EV = '2.16.840.1.114413.1.7.23.3';
    public const OID_LETSENCRYPT_DV = '1.3.6.1.4.1.44947.1.1.1';
    /** Sectigo acquired Comodo; same EV policy OID applies. */
    public const OID_SECTIGO_EV = self::OID_COMODO_EV;
    public const OID_BUYPASS_EV = '2.16.578.1.26.1.3.3';
    public const OID_CERTUM_EV = '1.2.616.1.113527.2.5.1.1';

    /**
     * Return all Extended Validation OIDs for seeding.
     *
     * @return array<string, array{name: string, short_name: ?string, description: string, category: string}>
     */
    public static function getAll(): array
    {
        return [
            // CA/Browser Forum Validation Levels
            self::OID_EV_GUIDELINES => [
                'name' => 'evGuidelines',
                'short_name' => 'EV',
                'description' => 'CA/Browser Forum Extended Validation Guidelines',
                'category' => 'x509',
            ],
            self::OID_DOMAIN_VALIDATED => [
                'name' => 'domainValidated',
                'short_name' => 'DV',
                'description' => 'CA/Browser Forum Domain Validated',
                'category' => 'x509',
            ],
            self::OID_ORGANIZATION_VALIDATED => [
                'name' => 'organizationValidated',
                'short_name' => 'OV',
                'description' => 'CA/Browser Forum Organization Validated',
                'category' => 'x509',
            ],
            self::OID_INDIVIDUAL_VALIDATED => [
                'name' => 'individualValidated',
                'short_name' => 'IV',
                'description' => 'CA/Browser Forum Individual Validated',
                'category' => 'x509',
            ],

            // Code Signing / S/MIME
            self::OID_CODE_SIGNING_REQUIREMENTS => [
                'name' => 'codeSigningRequirements',
                'short_name' => null,
                'description' => 'CA/Browser Forum Code Signing Requirements',
                'category' => 'x509',
            ],
            self::OID_SMIME_MAILBOX_VALIDATED => [
                'name' => 'smimeMailboxValidated',
                'short_name' => null,
                'description' => 'S/MIME Mailbox Validated',
                'category' => 'x509',
            ],
            self::OID_SMIME_ORGANIZATION_VALIDATED => [
                'name' => 'smimeOrganizationValidated',
                'short_name' => null,
                'description' => 'S/MIME Organization Validated',
                'category' => 'x509',
            ],
            self::OID_SMIME_SPONSOR_VALIDATED => [
                'name' => 'smimeSponsorValidated',
                'short_name' => null,
                'description' => 'S/MIME Sponsor Validated',
                'category' => 'x509',
            ],
            self::OID_SMIME_INDIVIDUAL_VALIDATED => [
                'name' => 'smimeIndividualValidated',
                'short_name' => null,
                'description' => 'S/MIME Individual Validated',
                'category' => 'x509',
            ],

            // Jurisdiction Attributes
            self::OID_JURISDICTION_LOCALITY => [
                'name' => 'jurisdictionLocality',
                'short_name' => null,
                'description' => 'EV Certificate Jurisdiction of Incorporation Locality',
                'category' => 'x509',
            ],
            self::OID_JURISDICTION_STATE => [
                'name' => 'jurisdictionState',
                'short_name' => null,
                'description' => 'EV Certificate Jurisdiction of Incorporation State',
                'category' => 'x509',
            ],
            self::OID_JURISDICTION_COUNTRY => [
                'name' => 'jurisdictionCountry',
                'short_name' => null,
                'description' => 'EV Certificate Jurisdiction of Incorporation Country',
                'category' => 'x509',
            ],
            self::OID_BUSINESS_CATEGORY => [
                'name' => 'businessCategory',
                'short_name' => null,
                'description' => 'X.520 Business Category',
                'category' => 'x509',
            ],

            // Major CA EV Policy OIDs
            self::OID_DIGICERT_EV => [
                'name' => 'digicertEV',
                'short_name' => null,
                'description' => 'DigiCert Extended Validation Policy',
                'category' => 'x509',
            ],
            self::OID_GLOBALSIGN_EV => [
                'name' => 'globalsignEV',
                'short_name' => null,
                'description' => 'GlobalSign Extended Validation Policy',
                'category' => 'x509',
            ],
            self::OID_COMODO_EV => [
                'name' => 'comodoEV',
                'short_name' => null,
                'description' => 'Comodo/Sectigo Extended Validation Policy',
                'category' => 'x509',
            ],
            self::OID_ENTRUST_EV => [
                'name' => 'entrustEV',
                'short_name' => null,
                'description' => 'Entrust Extended Validation Policy',
                'category' => 'x509',
            ],
            self::OID_GODADDY_EV => [
                'name' => 'godaddyEV',
                'short_name' => null,
                'description' => 'GoDaddy Extended Validation Policy',
                'category' => 'x509',
            ],
            self::OID_LETSENCRYPT_DV => [
                'name' => 'letsencryptDV',
                'short_name' => null,
                'description' => "Let's Encrypt Domain Validation Policy",
                'category' => 'x509',
            ],
            self::OID_BUYPASS_EV => [
                'name' => 'buypassEV',
                'short_name' => null,
                'description' => 'Buypass Extended Validation Policy',
                'category' => 'x509',
            ],
            self::OID_CERTUM_EV => [
                'name' => 'certumEV',
                'short_name' => null,
                'description' => 'Certum Extended Validation Policy',
                'category' => 'x509',
            ],
        ];
    }
}
