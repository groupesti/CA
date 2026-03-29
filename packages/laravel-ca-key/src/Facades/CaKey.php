<?php

declare(strict_types=1);

namespace CA\Key\Facades;

use CA\Key\Contracts\KeyManagerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \CA\Key\Models\Key generate(\CA\Enums\KeyAlgorithm $algorithm, array $params = [], ?string $tenantId = null)
 * @method static \CA\Key\Models\Key import(string $keyData, string $format, array $options = [])
 * @method static string export(\CA\Key\Models\Key $key, \CA\Enums\ExportFormat $format, ?string $passphrase = null)
 * @method static \CA\Key\Models\Key rotate(\CA\Key\Models\Key $key)
 * @method static void destroy(\CA\Key\Models\Key $key)
 * @method static \CA\Key\Models\Key|null getByFingerprint(string $fingerprint)
 * @method static \phpseclib3\Crypt\Common\PrivateKey decryptPrivateKey(\CA\Key\Models\Key $key, ?string $passphrase = null)
 *
 * @see \CA\Key\Services\KeyManager
 */
class CaKey extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return KeyManagerInterface::class;
    }
}
