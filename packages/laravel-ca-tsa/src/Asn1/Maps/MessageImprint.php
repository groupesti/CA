<?php

declare(strict_types=1);

namespace CA\Tsa\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * MessageImprint ::= SEQUENCE {
 *     hashAlgorithm  AlgorithmIdentifier,
 *     hashedMessage  OCTET STRING
 * }
 */
final class MessageImprint
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'hashAlgorithm' => [
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
                'hashedMessage' => [
                    'type' => ASN1::TYPE_OCTET_STRING,
                ],
            ],
        ];
    }
}
