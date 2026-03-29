<?php

declare(strict_types=1);

namespace CA\Tsa\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * Accuracy ::= SEQUENCE {
 *     seconds  INTEGER           OPTIONAL,
 *     millis   [0] INTEGER (1..999) OPTIONAL,
 *     micros   [1] INTEGER (1..999) OPTIONAL
 * }
 */
final class Accuracy
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'seconds' => [
                    'type' => ASN1::TYPE_INTEGER,
                    'optional' => true,
                ],
                'millis' => [
                    'type' => ASN1::TYPE_INTEGER,
                    'constant' => 0,
                    'optional' => true,
                    'implicit' => true,
                ],
                'micros' => [
                    'type' => ASN1::TYPE_INTEGER,
                    'constant' => 1,
                    'optional' => true,
                    'implicit' => true,
                ],
            ],
        ];
    }
}
