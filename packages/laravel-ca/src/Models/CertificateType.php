<?php

declare(strict_types=1);

namespace CA\Models;

class CertificateType extends Lookup
{
    protected static string $lookupType = 'certificate_type';

    public const ROOT_CA = 'root_ca';
    public const INTERMEDIATE_CA = 'intermediate_ca';
    public const SERVER_TLS = 'server_tls';
    public const CLIENT_MTLS = 'client_mtls';
    public const CODE_SIGNING = 'code_signing';
    public const SMIME = 'smime';
    public const DOMAIN_CONTROLLER = 'domain_controller';
    public const USER = 'user';
    public const COMPUTER = 'computer';
    public const CUSTOM = 'custom';

    public function isCa(): bool
    {
        return (bool) $this->meta('is_ca', false);
    }
}
