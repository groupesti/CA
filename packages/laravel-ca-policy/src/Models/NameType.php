<?php

declare(strict_types=1);

namespace CA\Policy\Models;

use CA\Models\Lookup;

class NameType extends Lookup
{
    protected static string $lookupType = 'name_type';

    public const DNS = 'dns';
    public const EMAIL = 'email';
    public const IP = 'ip';
    public const URI = 'uri';
    public const DIRECTORY_NAME = 'directoryName';
}
