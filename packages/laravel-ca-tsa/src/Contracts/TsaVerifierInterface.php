<?php

declare(strict_types=1);

namespace CA\Tsa\Contracts;

/**
 * Verifier for RFC 3161 timestamp responses.
 */
interface TsaVerifierInterface
{
    /**
     * Verify a TSR (TimeStampResp) DER response, optionally against original data.
     */
    public function verify(string $tsrDer, ?string $originalData = null): bool;

    /**
     * Extract and decode the TSTInfo from a TSR DER response.
     *
     * @return array{version: int, policy: string, hashAlgorithm: string, hashedMessage: string, serialNumber: string, genTime: string, accuracy: ?array, ordering: bool, nonce: ?string}
     */
    public function extractTstInfo(string $tsrDer): array;
}
