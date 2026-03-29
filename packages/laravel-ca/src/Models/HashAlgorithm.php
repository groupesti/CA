<?php

declare(strict_types=1);

namespace CA\Models;

class HashAlgorithm extends Lookup
{
    protected static string $lookupType = 'hash_algorithm';

    public const SHA256 = 'sha256';
    public const SHA384 = 'sha384';
    public const SHA512 = 'sha512';

    public function getOid(): string
    {
        return $this->meta('oid', '');
    }
}
