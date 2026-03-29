<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * RFC 6960 - TBSRequest structure.
 *
 * TBSRequest ::= SEQUENCE {
 *     version             [0]     EXPLICIT Version DEFAULT v1,
 *     requestorName       [1]     EXPLICIT GeneralName OPTIONAL,
 *     requestList                 SEQUENCE OF Request,
 *     requestExtensions   [2]     EXPLICIT Extensions OPTIONAL
 * }
 *
 * Request ::= SEQUENCE {
 *     reqCert                     CertID,
 *     singleRequestExtensions [0] EXPLICIT Extensions OPTIONAL
 * }
 */
class TBSRequest
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'version' => [
                    'type' => ASN1::TYPE_INTEGER,
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                    'default' => 'v1',
                    'mapping' => ['v1'],
                ],
                'requestorName' => [
                    'type' => ASN1::TYPE_ANY,
                    'constant' => 1,
                    'explicit' => true,
                    'optional' => true,
                ],
                'requestList' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'min' => 0,
                    'max' => -1,
                    'children' => self::requestMap(),
                ],
                'requestExtensions' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 2,
                    'explicit' => true,
                    'optional' => true,
                    'min' => 0,
                    'max' => -1,
                    'children' => self::extensionMap(),
                ],
            ],
        ];
    }

    private static function requestMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'reqCert' => CertID::getMap(),
                'singleRequestExtensions' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 0,
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
