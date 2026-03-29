<?php

declare(strict_types=1);

namespace CA\OCSP\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string handleRequest(string $derEncodedRequest)
 * @method static array parseRequest(string $derRequest)
 * @method static string buildResponse(array $singleResponses, \phpseclib3\Crypt\Common\PrivateKey $signingKey, \phpseclib3\File\X509 $responderCert, ?string $nonce)
 *
 * @see \CA\OCSP\Contracts\OcspResponderInterface
 */
class CaOcsp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ca-ocsp';
    }
}
