<?php

declare(strict_types=1);

namespace CA\Tsa\Contracts;

/**
 * RFC 3161 Time-Stamp Authority server interface.
 */
interface TsaServerInterface
{
    /**
     * Handle a raw TSQ (TimeStampReq) DER request and return a TSR (TimeStampResp) DER response.
     */
    public function handleRequest(string $tsqDer): string;

    /**
     * Parse a TSQ DER into its component fields.
     *
     * @return array{version: int, hashAlgorithm: string, hashedMessage: string, nonce: ?string, certReq: bool, policyOid: ?string}
     */
    public function parseRequest(string $tsqDer): array;

    /**
     * Create a timestamp token for the given parameters.
     *
     * @return string TSR DER bytes
     */
    public function createTimestamp(
        string $hashAlgorithm,
        string $hashedMessage,
        ?string $nonce,
        bool $certReq,
        ?string $policyOid,
    ): string;
}
