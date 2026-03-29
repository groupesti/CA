<?php

declare(strict_types=1);

namespace CA\Models;

class ChallengeType extends Lookup
{
    protected static string $lookupType = 'acme_challenge_type';

    public const HTTP_01 = 'http-01';
    public const DNS_01 = 'dns-01';
    public const TLS_ALPN_01 = 'tls-alpn-01';
}
