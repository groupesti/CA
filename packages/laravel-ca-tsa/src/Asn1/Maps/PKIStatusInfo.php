<?php

declare(strict_types=1);

namespace CA\Tsa\Asn1\Maps;

use phpseclib3\File\ASN1;

/**
 * PKIStatusInfo ::= SEQUENCE {
 *     status        PKIStatus,
 *     statusString  PKIFreeText OPTIONAL,
 *     failInfo      PKIFailureInfo OPTIONAL
 * }
 *
 * PKIStatus ::= INTEGER {
 *     granted                (0),
 *     grantedWithMods        (1),
 *     rejection              (2),
 *     waiting                (3),
 *     revocationWarning      (4),
 *     revocationNotification (5)
 * }
 *
 * PKIFailureInfo ::= BIT STRING {
 *     badAlg               (0),
 *     badRequest           (2),
 *     badDataFormat        (5),
 *     timeNotAvailable     (14),
 *     unacceptedPolicy     (15),
 *     unacceptedExtension  (16),
 *     addInfoNotAvailable  (17),
 *     systemFailure        (25)
 * }
 */
final class PKIStatusInfo
{
    // PKIStatus values
    public const STATUS_GRANTED = 0;
    public const STATUS_GRANTED_WITH_MODS = 1;
    public const STATUS_REJECTION = 2;
    public const STATUS_WAITING = 3;
    public const STATUS_REVOCATION_WARNING = 4;
    public const STATUS_REVOCATION_NOTIFICATION = 5;

    // PKIFailureInfo bit positions
    public const FAIL_BAD_ALG = 0;
    public const FAIL_BAD_REQUEST = 2;
    public const FAIL_BAD_DATA_FORMAT = 5;
    public const FAIL_TIME_NOT_AVAILABLE = 14;
    public const FAIL_UNACCEPTED_POLICY = 15;
    public const FAIL_UNACCEPTED_EXTENSION = 16;
    public const FAIL_ADD_INFO_NOT_AVAILABLE = 17;
    public const FAIL_SYSTEM_FAILURE = 25;

    public static function getMap(): array
    {
        return [
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => [
                'status' => [
                    'type' => ASN1::TYPE_INTEGER,
                ],
                'statusString' => [
                    'type' => ASN1::TYPE_SEQUENCE,
                    'optional' => true,
                    'min' => 1,
                    'max' => -1,
                    'children' => [
                        'type' => ASN1::TYPE_UTF8_STRING,
                    ],
                ],
                'failInfo' => [
                    'type' => ASN1::TYPE_BIT_STRING,
                    'optional' => true,
                ],
            ],
        ];
    }
}
