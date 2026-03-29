<?php

declare(strict_types=1);

namespace CA\Scep\Contracts;

use CA\Scep\Services\ScepMessage;

interface ScepMessageParserInterface
{
    /**
     * Parse a DER-encoded PKCS#7 SignedData SCEP message.
     */
    public function parse(string $derMessage): ScepMessage;

    /**
     * Extract the PKCS#10 CSR from a parsed SCEP message.
     */
    public function extractCsr(ScepMessage $message): string;

    /**
     * Extract the challenge password from a parsed SCEP message.
     */
    public function extractChallenge(ScepMessage $message): ?string;
}
