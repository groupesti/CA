<?php

declare(strict_types=1);

namespace CA\Key\Generators;

use CA\Models\KeyAlgorithm;
use CA\Key\Contracts\KeyGeneratorInterface;
use phpseclib3\Crypt\EC;

final class EcdsaKeyGenerator implements KeyGeneratorInterface
{
    /**
     * @param  array<string, mixed>  $parameters
     * @return array{privateKey: \phpseclib3\Crypt\EC\PrivateKey, publicKey: \phpseclib3\Crypt\EC\PublicKey}
     */
    public function generate(array $parameters = []): array
    {
        $curve = $parameters['curve'] ?? 'secp256r1';

        /** @var \phpseclib3\Crypt\EC\PrivateKey $privateKey */
        $privateKey = EC::createKey($curve);
        $publicKey = $privateKey->getPublicKey();

        return [
            'privateKey' => $privateKey,
            'publicKey' => $publicKey,
        ];
    }

    public function supports(string $algorithm): bool
    {
        return in_array($algorithm, [
            KeyAlgorithm::ECDSA_P256,
            KeyAlgorithm::ECDSA_P384,
            KeyAlgorithm::ECDSA_P521,
        ], true);
    }

    public function getAlgorithm(): string
    {
        return KeyAlgorithm::ECDSA_P256;
    }
}
