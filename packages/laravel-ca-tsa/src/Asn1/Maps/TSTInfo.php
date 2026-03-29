<?php

declare(strict_types=1);

namespace CA\Tsa\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * TSTInfo ::= SEQUENCE {
 *     version        INTEGER { v1(1) },
 *     policy         TSAPolicyId,
 *     messageImprint MessageImprint,
 *     serialNumber   INTEGER,
 *     genTime        GeneralizedTime,
 *     accuracy       Accuracy OPTIONAL,
 *     ordering       BOOLEAN DEFAULT FALSE,
 *     nonce          INTEGER OPTIONAL,
 *     tsa            [0] GeneralName OPTIONAL,
 *     extensions     [1] IMPLICIT Extensions OPTIONAL
 * }
 */
final class TSTInfo
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'version' => [
                    'type' => ASN1::TYPE_INTEGER,
                ],
                'policy' => [
                    'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                ],
                'messageImprint' => MessageImprint::getMap(),
                'serialNumber' => [
                    'type' => ASN1::TYPE_INTEGER,
                ],
                'genTime' => [
                    'type' => ASN1::TYPE_GENERALIZED_TIME,
                ],
                'accuracy' => array_merge(Accuracy::getMap(), [
                    'optional' => true,
                ]),
                'ordering' => [
                    'type' => ASN1::TYPE_BOOLEAN,
                    'optional' => true,
                    'default' => false,
                ],
                'nonce' => [
                    'type' => ASN1::TYPE_INTEGER,
                    'optional' => true,
                ],
                'tsa' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 0,
                    'optional' => true,
                    'explicit' => true,
                    'children' => [
                        'directoryName' => [
                            'type' => ASN1::TYPE_SEQUENCE,
                            'constant' => 4,
                            'optional' => true,
                            'implicit' => true,
                            'children' => [
                                'rdnSequence' => [
                                    'type' => ASN1::TYPE_SET,
                                    'min' => 1,
                                    'max' => -1,
                                    'children' => [
                                        'type' => ASN1::TYPE_SEQUENCE,
                                        'children' => [
                                            'type' => [
                                                'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                                            ],
                                            'value' => [
                                                'type' => ASN1::TYPE_ANY,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'extensions' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 1,
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
