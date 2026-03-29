<?php

declare(strict_types=1);

namespace CA\Models;

class ScepFailInfo extends Lookup
{
    protected static string $lookupType = 'scep_fail_info';

    public const BAD_ALG = 'bad_alg';
    public const BAD_MESSAGE_CHECK = 'bad_message_check';
    public const BAD_REQUEST = 'bad_request';
    public const BAD_TIME = 'bad_time';
    public const BAD_CERT_ID = 'bad_cert_id';
}
