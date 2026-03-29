<?php

declare(strict_types=1);

namespace CA\Scep\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * ASN.1 Maps for SCEP PKI Messages.
 *
 * SCEP uses CMS/PKCS#7 SignedData + EnvelopedData structures with
 * custom authenticated attributes identified by specific OIDs.
 */
final class ScepPkiMessage
{
    // SCEP Attribute OIDs
    public const OID_MESSAGE_TYPE = '2.16.840.1.113733.1.9.2';
    public const OID_PKI_STATUS = '2.16.840.1.113733.1.9.3';
    public const OID_FAIL_INFO = '2.16.840.1.113733.1.9.4';
    public const OID_SENDER_NONCE = '2.16.840.1.113733.1.9.5';
    public const OID_RECIPIENT_NONCE = '2.16.840.1.113733.1.9.6';
    public const OID_TRANSACTION_ID = '2.16.840.1.113733.1.9.7';

    // CMS/PKCS#7 Content Type OIDs
    public const OID_SIGNED_DATA = '1.2.840.113549.1.7.2';
    public const OID_ENVELOPED_DATA = '1.2.840.113549.1.7.3';
    public const OID_DATA = '1.2.840.113549.1.7.1';

    // Algorithm OIDs
    public const OID_RSA_ENCRYPTION = '1.2.840.113549.1.1.1';
    public const OID_SHA256_WITH_RSA = '1.2.840.113549.1.1.11';
    public const OID_SHA512_WITH_RSA = '1.2.840.113549.1.1.13';
    public const OID_SHA256 = '2.16.840.1.101.3.4.2.1';
    public const OID_SHA512 = '2.16.840.1.101.3.4.2.3';
    public const OID_AES_256_CBC = '2.16.840.1.101.3.4.1.42';
    public const OID_AES_128_CBC = '2.16.840.1.101.3.4.1.2';
    public const OID_DES_EDE3_CBC = '1.2.840.113549.3.7';

    // Attribute OIDs (standard CMS)
    public const OID_CONTENT_TYPE = '1.2.840.113549.1.9.3';
    public const OID_MESSAGE_DIGEST = '1.2.840.113549.1.9.4';

    /**
     * ContentInfo ::= SEQUENCE {
     *   contentType ContentType,
     *   content [0] EXPLICIT ANY DEFINED BY contentType
     * }
     */
    public static function contentInfoMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'contentType' => ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                'content' => [
                    'type' => ASN1::TYPE_ANY,
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                ],
            ],
        ];
    }

    /**
     * SignedData ::= SEQUENCE {
     *   version CMSVersion,
     *   digestAlgorithms DigestAlgorithmIdentifiers,
     *   encapContentInfo EncapsulatedContentInfo,
     *   certificates [0] IMPLICIT CertificateSet OPTIONAL,
     *   crls [1] IMPLICIT RevocationInfoChoices OPTIONAL,
     *   signerInfos SignerInfos
     * }
     */
    public static function signedDataMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'version' => ['type' => ASN1::TYPE_INTEGER],
                'digestAlgorithms' => [
                    'type' => ASN1::TYPE_SET,
                    'min' => 0,
                    'max' => -1,
                    'children' => self::algorithmIdentifierMap(),
                ],
                'encapContentInfo' => self::encapsulatedContentInfoMap(),
                'certificates' => [
                    'type' => ASN1::TYPE_ANY,
                    'constant' => 0,
                    'implicit' => true,
                    'optional' => true,
                ],
                'crls' => [
                    'type' => ASN1::TYPE_ANY,
                    'constant' => 1,
                    'implicit' => true,
                    'optional' => true,
                ],
                'signerInfos' => [
                    'type' => ASN1::TYPE_SET,
                    'min' => 0,
                    'max' => -1,
                    'children' => self::signerInfoMap(),
                ],
            ],
        ];
    }

    /**
     * EncapsulatedContentInfo ::= SEQUENCE {
     *   eContentType ContentType,
     *   eContent [0] EXPLICIT OCTET STRING OPTIONAL
     * }
     */
    public static function encapsulatedContentInfoMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'eContentType' => ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                'eContent' => [
                    'type' => ASN1::TYPE_OCTET_STRING,
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                ],
            ],
        ];
    }

    /**
     * SignerInfo ::= SEQUENCE {
     *   version CMSVersion,
     *   sid SignerIdentifier,
     *   digestAlgorithm DigestAlgorithmIdentifier,
     *   signedAttrs [0] IMPLICIT SignedAttributes OPTIONAL,
     *   signatureAlgorithm SignatureAlgorithmIdentifier,
     *   signature SignatureValue,
     *   unsignedAttrs [1] IMPLICIT UnsignedAttributes OPTIONAL
     * }
     */
    public static function signerInfoMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'version' => ['type' => ASN1::TYPE_INTEGER],
                'sid' => self::issuerAndSerialNumberMap(),
                'digestAlgorithm' => self::algorithmIdentifierMap(),
                'signedAttrs' => [
                    'type' => ASN1::TYPE_ANY,
                    'constant' => 0,
                    'implicit' => true,
                    'optional' => true,
                ],
                'signatureAlgorithm' => self::algorithmIdentifierMap(),
                'signature' => ['type' => ASN1::TYPE_OCTET_STRING],
                'unsignedAttrs' => [
                    'type' => ASN1::TYPE_ANY,
                    'constant' => 1,
                    'implicit' => true,
                    'optional' => true,
                ],
            ],
        ];
    }

    /**
     * IssuerAndSerialNumber ::= SEQUENCE {
     *   issuer Name,
     *   serialNumber CertificateSerialNumber
     * }
     */
    public static function issuerAndSerialNumberMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'issuer' => ['type' => ASN1::TYPE_ANY],
                'serialNumber' => ['type' => ASN1::TYPE_INTEGER],
            ],
        ];
    }

    /**
     * AlgorithmIdentifier ::= SEQUENCE {
     *   algorithm OBJECT IDENTIFIER,
     *   parameters ANY OPTIONAL
     * }
     */
    public static function algorithmIdentifierMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'algorithm' => ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                'parameters' => [
                    'type' => ASN1::TYPE_ANY,
                    'optional' => true,
                ],
            ],
        ];
    }

    /**
     * EnvelopedData ::= SEQUENCE {
     *   version CMSVersion,
     *   recipientInfos RecipientInfos,
     *   encryptedContentInfo EncryptedContentInfo
     * }
     */
    public static function envelopedDataMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'version' => ['type' => ASN1::TYPE_INTEGER],
                'recipientInfos' => [
                    'type' => ASN1::TYPE_SET,
                    'min' => 1,
                    'max' => -1,
                    'children' => self::recipientInfoMap(),
                ],
                'encryptedContentInfo' => self::encryptedContentInfoMap(),
            ],
        ];
    }

    /**
     * RecipientInfo (KeyTransRecipientInfo) ::= SEQUENCE {
     *   version CMSVersion,
     *   rid RecipientIdentifier,
     *   keyEncryptionAlgorithm KeyEncryptionAlgorithmIdentifier,
     *   encryptedKey EncryptedKey
     * }
     */
    public static function recipientInfoMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'version' => ['type' => ASN1::TYPE_INTEGER],
                'rid' => self::issuerAndSerialNumberMap(),
                'keyEncryptionAlgorithm' => self::algorithmIdentifierMap(),
                'encryptedKey' => ['type' => ASN1::TYPE_OCTET_STRING],
            ],
        ];
    }

    /**
     * EncryptedContentInfo ::= SEQUENCE {
     *   contentType ContentType,
     *   contentEncryptionAlgorithm ContentEncryptionAlgorithmIdentifier,
     *   encryptedContent [0] IMPLICIT OCTET STRING OPTIONAL
     * }
     */
    public static function encryptedContentInfoMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'contentType' => ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                'contentEncryptionAlgorithm' => self::algorithmIdentifierMap(),
                'encryptedContent' => [
                    'type' => ASN1::TYPE_OCTET_STRING,
                    'constant' => 0,
                    'implicit' => true,
                    'optional' => true,
                ],
            ],
        ];
    }

    /**
     * Attribute ::= SEQUENCE {
     *   attrType OBJECT IDENTIFIER,
     *   attrValues SET OF AttributeValue
     * }
     */
    public static function attributeMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'type' => ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                'values' => [
                    'type' => ASN1::TYPE_SET,
                    'min' => 1,
                    'max' => -1,
                    'children' => ['type' => ASN1::TYPE_ANY],
                ],
            ],
        ];
    }

    /**
     * Get the OID-to-name mapping for SCEP attributes.
     */
    public static function getScepOidMap(): array
    {
        return [
            self::OID_MESSAGE_TYPE => 'id-scep-messageType',
            self::OID_PKI_STATUS => 'id-scep-pkiStatus',
            self::OID_FAIL_INFO => 'id-scep-failInfo',
            self::OID_SENDER_NONCE => 'id-scep-senderNonce',
            self::OID_RECIPIENT_NONCE => 'id-scep-recipientNonce',
            self::OID_TRANSACTION_ID => 'id-scep-transactionID',
            self::OID_CONTENT_TYPE => 'id-contentType',
            self::OID_MESSAGE_DIGEST => 'id-messageDigest',
        ];
    }

    /**
     * Get the encryption algorithm OID for a given algorithm name.
     */
    public static function getEncryptionAlgorithmOid(string $algorithm): string
    {
        return match (strtolower($algorithm)) {
            'aes-256-cbc' => self::OID_AES_256_CBC,
            'aes-128-cbc' => self::OID_AES_128_CBC,
            '3des', 'des-ede3-cbc' => self::OID_DES_EDE3_CBC,
            default => self::OID_AES_256_CBC,
        };
    }

    /**
     * Get the hash algorithm OID for a given algorithm name.
     */
    public static function getHashAlgorithmOid(string $hash): string
    {
        return match (strtolower($hash)) {
            'sha256' => self::OID_SHA256,
            'sha512' => self::OID_SHA512,
            default => self::OID_SHA256,
        };
    }

    /**
     * Get the signature algorithm OID for a given hash algorithm.
     */
    public static function getSignatureAlgorithmOid(string $hash): string
    {
        return match (strtolower($hash)) {
            'sha256' => self::OID_SHA256_WITH_RSA,
            'sha512' => self::OID_SHA512_WITH_RSA,
            default => self::OID_SHA256_WITH_RSA,
        };
    }
}
