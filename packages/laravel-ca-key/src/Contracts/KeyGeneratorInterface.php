<?php

declare(strict_types=1);

namespace CA\Key\Contracts;

interface KeyGeneratorInterface
{
    /**
     * Generate a key pair.
     *
     * @param  array<string, mixed>  $parameters
     * @return array{privateKey: \phpseclib3\Crypt\Common\PrivateKey, publicKey: \phpseclib3\Crypt\Common\PublicKey}
     */
    public function generate(array $parameters = []): array;

    /**
     * Check if this generator supports the given algorithm slug.
     */
    public function supports(string $algorithm): bool;

    /**
     * Get the primary algorithm slug this generator handles.
     */
    public function getAlgorithm(): string;
}
