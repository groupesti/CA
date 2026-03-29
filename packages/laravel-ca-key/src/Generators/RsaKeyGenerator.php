<?php

declare(strict_types=1);

namespace CA\Key\Generators;

use CA\Models\KeyAlgorithm;
use CA\Key\Contracts\KeyGeneratorInterface;
use phpseclib3\Crypt\RSA;

final class RsaKeyGenerator implements KeyGeneratorInterface
{
    public function __construct(
        private readonly int $defaultBits = 4096,
    ) {}

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{privateKey: \phpseclib3\Crypt\RSA\PrivateKey, publicKey: \phpseclib3\Crypt\RSA\PublicKey}
     */
    public function generate(array $parameters = []): array
    {
        $bits = (int) ($parameters['bits'] ?? $this->defaultBits);

        /** @var \phpseclib3\Crypt\RSA\PrivateKey $privateKey */
        $privateKey = RSA::createKey($bits);
        $publicKey = $privateKey->getPublicKey();

        return [
            'privateKey' => $privateKey,
            'publicKey' => $publicKey,
        ];
    }

    public function supports(string $algorithm): bool
    {
        return in_array($algorithm, [KeyAlgorithm::RSA_2048, KeyAlgorithm::RSA_4096], true);
    }

    public function getAlgorithm(): string
    {
        return match ($this->defaultBits) {
            2048 => KeyAlgorithm::RSA_2048,
            default => KeyAlgorithm::RSA_4096,
        };
    }
}
