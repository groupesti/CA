<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * RFC 6960 - SingleResponse structure.
 *
 * SingleResponse ::= SEQUENCE {
 *     certID                       CertID,
 *     certStatus                   CertStatus,
 *     thisUpdate                   GeneralizedTime,
 *     nextUpdate         [0]       EXPLICIT GeneralizedTime OPTIONAL,
 *     singleExtensions   [1]       EXPLICIT Extensions OPTIONAL
 * }
 */
class SingleResponse
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'certID' => CertID::getMap(),
                'certStatus' => CertStatus::getMap(),
                'thisUpdate' => [
                    'type' => ASN1::TYPE_GENERALIZED_TIME,
                ],
                'nextUpdate' => [
                    'type' => ASN1::TYPE_GENERALIZED_TIME,
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                ],
                'singleExtensions' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 1,
                    'explicit' => true,
                    'optional' => true,
                    'min' => 0,
                    'max' => -1,
                    'children' => self::extensionMap(),
                ],
            ],
        ];
    }

    private static function extensionMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'extnId' => [
                    'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                ],
                'critical' => [
                    'type' => ASN1::TYPE_BOOLEAN,
                    'optional' => true,
                    'default' => false,
                ],
                'extnValue' => [
                    'type' => ASN1::TYPE_OCTET_STRING,
                ],
            ],
        ];
    }
}
