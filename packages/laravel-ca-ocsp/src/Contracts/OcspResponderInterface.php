<?php

declare(strict_types=1);

namespace CA\OCSP\Contracts;

use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\File\X509;

interface OcspResponderInterface
{
    /**
     * Handle a raw DER-encoded OCSP request and return a DER-encoded OCSP response.
     */
    public function handleRequest(string $derEncodedRequest): string;

    /**
     * Parse a DER-encoded OCSP request into a structured array.
     *
     * @return array{requests: array<int, array{issuerNameHash: string, issuerKeyHash: string, serialNumber: string, hashAlgorithm: string}>, nonce: ?string}
     */
    public function parseRequest(string $derRequest): array;

    /**
     * Build a DER-encoded OCSP response from single responses.
     *
     * @param  array<int, array>  $singleResponses
     */
    public function buildResponse(
        array $singleResponses,
        PrivateKey $signingKey,
        X509 $responderCert,
        ?string $nonce,
    ): string;
}
