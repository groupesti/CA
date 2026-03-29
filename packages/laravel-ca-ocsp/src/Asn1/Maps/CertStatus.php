<?php

declare(strict_types=1);

namespace CA\OCSP\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * RFC 6960 - CertStatus structure.
 *
 * CertStatus ::= CHOICE {
 *     good        [0]     IMPLICIT NULL,
 *     revoked     [1]     IMPLICIT RevokedInfo,
 *     unknown     [2]     IMPLICIT NULL
 * }
 *
 * RevokedInfo ::= SEQUENCE {
 *     revocationTime              GeneralizedTime,
 *     revocationReason    [0]     EXPLICIT CRLReason OPTIONAL
 * }
 */
class CertStatus
{
    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_CHOICE,
            'children' => [
                'good' => [
                    'type' => ASN1::TYPE_NULL,
                    'constant' => 0,
                    'implicit' => true,
                ],
                'revoked' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 1,
                    'implicit' => true,
                    'children' => [
                        'revocationTime' => [
                            'type' => ASN1::TYPE_GENERALIZED_TIME,
                        ],
                        'revocationReason' => [
                            'type' => ASN1::TYPE_ENUMERATED,
                            'constant' => 0,
                            'explicit' => true,
                            'optional' => true,
                            'mapping' => [
                                0 => 'unspecified',
                                1 => 'keyCompromise',
                                2 => 'cACompromise',
                                3 => 'affiliationChanged',
                                4 => 'superseded',
                                5 => 'cessationOfOperation',
                                6 => 'certificateHold',
                                8 => 'removeFromCRL',
                                9 => 'privilegeWithdrawn',
                                10 => 'aACompromise',
                            ],
                        ],
                    ],
                ],
                'unknown' => [
                    'type' => ASN1::TYPE_NULL,
                    'constant' => 2,
                    'implicit' => true,
                ],
            ],
        ];
    }
}
