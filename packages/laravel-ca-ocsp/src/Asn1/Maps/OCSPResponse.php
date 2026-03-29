<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * RFC 6960 - OCSPResponse structure.
 *
 * OCSPResponse ::= SEQUENCE {
 *     responseStatus         OCSPResponseStatus,
 *     responseBytes      [0] EXPLICIT ResponseBytes OPTIONAL
 * }
 *
 * OCSPResponseStatus ::= ENUMERATED {
 *     successful            (0),
 *     malformedRequest      (1),
 *     internalError         (2),
 *     tryLater              (3),
 *     -- (4) is not used
 *     sigRequired           (5),
 *     unauthorized          (6)
 * }
 *
 * ResponseBytes ::= SEQUENCE {
 *     responseType   OBJECT IDENTIFIER,
 *     response       OCTET STRING
 * }
 */
class OCSPResponse
{
    public const STATUS_SUCCESSFUL = 0;
    public const STATUS_MALFORMED_REQUEST = 1;
    public const STATUS_INTERNAL_ERROR = 2;
    public const STATUS_TRY_LATER = 3;
    public const STATUS_SIG_REQUIRED = 5;
    public const STATUS_UNAUTHORIZED = 6;

    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'responseStatus' => [
                    'type' => ASN1::TYPE_ENUMERATED,
                    'mapping' => [
                        0 => 'successful',
                        1 => 'malformedRequest',
                        2 => 'internalError',
                        3 => 'tryLater',
                        4 => 'unused',
                        5 => 'sigRequired',
                        6 => 'unauthorized',
                    ],
                ],
                'responseBytes' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                    'children' => [
                        'responseType' => [
                            'type' => ASN1::TYPE_OBJECT_IDENTIFIER,
                        ],
                        'response' => [
                            'type' => ASN1::TYPE_OCTET_STRING,
                        ],
                    ],
                ],
            ],
        ];
    }
}
