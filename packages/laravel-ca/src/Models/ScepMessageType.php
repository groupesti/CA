<?php

declare(strict_types=1);

namespace CA\Models;

class ScepMessageType extends Lookup
{
    protected static string $lookupType = 'scep_message_type';

    public const PKCS_REQ = 'pkcs_req';
    public const CERT_REP = 'cert_rep';
    public const GET_CERT_INITIAL = 'get_cert_initial';
    public const GET_CERT = 'get_cert';
    public const GET_CRL = 'get_crl';

    /**
     * Map SCEP protocol integer message type values to slugs.
     */
    private const NUMERIC_MAP = [
        19 => self::PKCS_REQ,
        3 => self::CERT_REP,
        20 => self::GET_CERT_INITIAL,
        21 => self::GET_CERT,
        22 => self::GET_CRL,
    ];

    public static function fromNumericValue(int $value): static
    {
        $slug = self::NUMERIC_MAP[$value] ?? null;

        if ($slug === null) {
            throw new \RuntimeException("Unknown SCEP message type numeric value: {$value}");
        }

        return static::fromSlug($slug);
    }
}
