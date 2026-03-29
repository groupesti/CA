<?php

declare(strict_types=1);

namespace CA\Tsa\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * TimeStampReq ::= SEQUENCE {
 *     version        INTEGER { v1(1) },
 *     messageImprint MessageImprint,
 *     reqPolicy      TSAPolicyId OPTIONAL,
 *     nonce          INTEGER OPTIONAL,
 *     certReq        BOOLEAN DEFAULT FALSE,
 *     extensions     [0] IMPLICIT Extensions OPTIONAL
 * }
 */
final class TimeStampReq
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'version' => [
                    'type' => ASN1::TYPE_INTEGER,
                ],
                'messageImprint' => MessageImprint::getMap(),
                'reqPolicy' => [
                    'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                    'optional' => true,
                ],
                'nonce' => [
                    'type' => ASN1::TYPE_INTEGER,
                    'optional' => true,
                ],
                'certReq' => [
                    'type' => ASN1::TYPE_BOOLEAN,
                    'optional' => true,
                    'default' => false,
                ],
                'extensions' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 0,
                    'optional' => true,
                    'implicit' => true,
                    'min' => 1,
                    'max' => -1,
                    'children' => [
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
                    ],
                ],
            ],
        ];
    }
}
