<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * RFC 6960 - ResponseData structure.
 *
 * ResponseData ::= SEQUENCE {
 *     version              [0] EXPLICIT Version DEFAULT v1,
 *     responderID              ResponderID,
 *     producedAt               GeneralizedTime,
 *     responses                SEQUENCE OF SingleResponse,
 *     responseExtensions   [1] EXPLICIT Extensions OPTIONAL
 * }
 *
 * ResponderID ::= CHOICE {
 *     byName               [1] Name,
 *     byKey                [2] KeyHash
 * }
 *
 * KeyHash ::= OCTET STRING
 */
class ResponseData
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
                'responderID' => [
                    'type' => ASN1::TYPE_CHOICE,
                    'children' => [
                        'byName' => [
                            'type' => ASN1::TYPE_ANY,
                            'constant' => 1,
                            'explicit' => true,
                        ],
                        'byKey' => [
                            'type' => ASN1::TYPE_OCTET_STRING,
                            'constant' => 2,
                            'explicit' => true,
                        ],
                    ],
                ],
                'producedAt' => [
                    'type' => ASN1::TYPE_GENERALIZED_TIME,
                ],
                'responses' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'min' => 0,
                    'max' => -1,
                    'children' => SingleResponse::getMap(),
                ],
                'responseExtensions' => [
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
