<?php

declare(strict_types=1);

namespace CA\Scep\Models;

use CA\Models\Lookup;

class ScepMessageType extends Lookup
{
    protected static string $lookupType = 'scep_message_type';

    public const CERT_REP = 'cert_rep';
    public const RENEWAL_REQ = 'renewal_req';
    public const PKCS_REQ = 'pkcs_req';
    public const CERT_POLL = 'cert_poll';
    public const GET_CERT = 'get_cert';
    public const GET_CRL = 'get_crl';

    public function getScepCode(): int
    {
        return $this->numeric_value ?? 0;
    }
}
