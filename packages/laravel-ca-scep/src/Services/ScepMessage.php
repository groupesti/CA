<?php

declare(strict_types=1);

namespace CA\Scep\Services;

use CA\Models\ScepMessageType;

/**
 * Data transfer object representing a parsed SCEP message.
 */
final class ScepMessage
{
    public function __construct(
        public readonly ScepMessageType $messageType,
        public readonly string $transactionId,
        public readonly string $senderNonce,
        public readonly string $signerCertificateDer,
        public readonly string $encryptedContent,
        public readonly ?string $decryptedContent = null,
        public readonly ?string $digestAlgorithm = null,
        public readonly ?string $signatureAlgorithm = null,
        public readonly array $rawSignedAttributes = [],
        public readonly ?string $signature = null,
    ) {}

    /**
     * Create a new instance with decrypted content set.
     */
    public function withDecryptedContent(string $decryptedContent): self
    {
        return new self(
            messageType: $this->messageType,
            transactionId: $this->transactionId,
            senderNonce: $this->senderNonce,
            signerCertificateDer: $this->signerCertificateDer,
            encryptedContent: $this->encryptedContent,
            decryptedContent: $decryptedContent,
            digestAlgorithm: $this->digestAlgorithm,
            signatureAlgorithm: $this->signatureAlgorithm,
            rawSignedAttributes: $this->rawSignedAttributes,
            signature: $this->signature,
        );
    }
}
