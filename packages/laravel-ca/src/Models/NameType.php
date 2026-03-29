<?php

declare(strict_types=1);

namespace CA\Models;

class NameType extends Lookup
{
    protected static string $lookupType = 'name_type';

    public const DNS = 'dns';
    public const EMAIL = 'email';
    public const IP = 'ip';
    public const URI = 'uri';
    public const DIRECTORY = 'directory';
    public const DIRECTORY_NAME = 'directory';
}
