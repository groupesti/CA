<?php

declare(strict_types=1);

namespace CA\Key\Generators;

use CA\Models\KeyAlgorithm;
use CA\Key\Contracts\KeyGeneratorInterface;
use phpseclib3\Crypt\EC;

final class Ed25519KeyGenerator implements KeyGeneratorInterface
{
    /**
     * @param  array<string, mixed>  $parameters
     * @return array{privateKey: \phpseclib3\Crypt\EC\PrivateKey, publicKey: \phpseclib3\Crypt\EC\PublicKey}
     */
    public function generate(array $parameters = []): array
    {
        /** @var \phpseclib3\Crypt\EC\PrivateKey $privateKey */
        $privateKey = EC::createKey('Ed25519');
        $publicKey = $privateKey->getPublicKey();

        return [
            'privateKey' => $privateKey,
            'publicKey' => $publicKey,
        ];
    }

    public function supports(string $algorithm): bool
    {
        return $algorithm === KeyAlgorithm::ED25519;
    }

    public function getAlgorithm(): string
    {
        return KeyAlgorithm::ED25519;
    }
}
