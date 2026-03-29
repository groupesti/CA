<?php

declare(strict_types=1);

namespace CA\Tsa\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * TimeStampResp ::= SEQUENCE {
 *     status          PKIStatusInfo,
 *     timeStampToken  ContentInfo OPTIONAL
 * }
 *
 * ContentInfo ::= SEQUENCE {
 *     contentType ContentType,
 *     content     [0] EXPLICIT ANY DEFINED BY contentType
 * }
 */
final class TimeStampResp
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'status' => PKIStatusInfo::getMap(),
                'timeStampToken' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'optional' => true,
                    'children' => [
                        'contentType' => [
                            'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                        ],
                        'content' => [
                            'type' => ASN1::TYPE_ANY,
                            'constant' => 0,
                            'explicit' => true,
                            'optional' => true,
                        ],
                    ],
                ],
            ],
        ];
    }
}
