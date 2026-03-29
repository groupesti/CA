<?php

declare(strict_types=1);

namespace CA\Tsa\Facades;

use CA\Tsa\Contracts\TsaServerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string handleRequest(string $tsqDer)
 * @method static array parseRequest(string $tsqDer)
 * @method static string createTimestamp(string $hashAlgorithm, string $hashedMessage, ?string $nonce, bool $certReq, ?string $policyOid)
 *
 * @see \CA\Tsa\Services\TsaServer
 */
class CaTsa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TsaServerInterface::class;
    }
}
