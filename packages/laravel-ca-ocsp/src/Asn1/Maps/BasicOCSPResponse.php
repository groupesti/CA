<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * RFC 6960 - BasicOCSPResponse structure.
 *
 * BasicOCSPResponse ::= SEQUENCE {
 *     tbsResponseData      ResponseData,
 *     signatureAlgorithm   AlgorithmIdentifier,
 *     signature            BIT STRING,
 *     certs            [0] EXPLICIT SEQUENCE OF Certificate OPTIONAL
 * }
 */
class BasicOCSPResponse
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'tbsResponseData' => ResponseData::getMap(),
                'signatureAlgorithm' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'children' => [
                        'algorithm' => [
                            'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                        ],
                        'parameters' => [
                            'type' => ASN1::TYPE_ANY,
                            'optional' => true,
                        ],
                    ],
                ],
                'signature' => [
                    'type' => ASN1::TYPE_BIT_STRING,
                ],
                'certs' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                    'min' => 0,
                    'max' => -1,
                    'children' => [
                        'type' => ASN1::TYPE_ANY,
                    ],
                ],
            ],
        ];
    }
}
