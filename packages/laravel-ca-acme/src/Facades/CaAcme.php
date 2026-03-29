<?php

declare(strict_types=1);

namespace CA\Acme\Facades;

use CA\Acme\Contracts\AcmeServerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array directory()
 * @method static string newNonce()
 * @method static \CA\Acme\Models\AcmeAccount newAccount(array $payload, string $jwk)
 * @method static \CA\Acme\Models\AcmeOrder newOrder(\CA\Acme\Models\AcmeAccount $account, array $identifiers)
 * @method static \CA\Acme\Models\AcmeOrder getOrder(string $url)
 * @method static \CA\Acme\Models\AcmeOrder finalizeOrder(\CA\Acme\Models\AcmeOrder $order, string $csr)
 * @method static string getCertificate(\CA\Acme\Models\AcmeOrder $order)
 * @method static void revokeCertificate(string $certPem, ?int $reason = null)
 *
 * @see \CA\Acme\Services\AcmeServer
 */
class CaAcme extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AcmeServerInterface::class;
    }
}
