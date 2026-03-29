<?php

declare(strict_types=1);

namespace CA\Models;

class RevocationReason extends Lookup
{
    protected static string $lookupType = 'revocation_reason';

    public const UNSPECIFIED = 'unspecified';
    public const KEY_COMPROMISE = 'key_compromise';
    public const CA_COMPROMISE = 'ca_compromise';
    public const AFFILIATION_CHANGED = 'affiliation_changed';
    public const SUPERSEDED = 'superseded';
    public const CESSATION_OF_OPERATION = 'cessation_of_operation';
    public const CERTIFICATE_HOLD = 'certificate_hold';
    public const REMOVE_FROM_CRL = 'remove_from_crl';
    public const PRIVILEGE_WITHDRAWN = 'privilege_withdrawn';
    public const AA_COMPROMISE = 'aa_compromise';

    public function label(): string
    {
        return $this->name;
    }

    public function getRfcCode(): int
    {
        return $this->numeric_value ?? 0;
    }
}
