<?php

declare(strict_types=1);

namespace CA\Crt\Builders;

use CA\DTOs\CertificateOptions;
use CA\Models\CertificateType;
use CA\Models\HashAlgorithm;

final class ClientCertificateBuilder
{
    /** @var array<int, array<string, string>> */
    private array $sanEntries = [];

    private int $validityDays;

    private HashAlgorithm $hashAlgorithm;

    /** @var array<int, string> */
    private array $keyUsage = ['digitalSignature'];

    /** @var array<int, string> */
    private array $extendedKeyUsage = ['clientAuth'];

    /** @var array<int, array{oid: string, critical: bool, value: mixed}> */
    private array $customExtensions = [];

    private ?string $templateId = null;

    public function __construct()
    {
        $this->validityDays = (int) config('ca-crt.default_validity_days', 365);
        $this->hashAlgorithm = HashAlgorithm::from((string) config('ca-crt.default_hash', 'sha256'));
    }

    public function setEmail(string $email): self
    {
        $this->sanEntries[] = ['rfc822Name' => $email];

        return $this;
    }

    public function setUserId(string $userId): self
    {
        $this->customExtensions[] = [
            'oid' => '2.5.4.45', // X.520 UniqueIdentifier
            'critical' => false,
            'value' => $userId,
        ];

        return $this;
    }

    public function setValidityDays(int $days): self
    {
        $this->validityDays = $days;

        return $this;
    }

    public function setHashAlgorithm(HashAlgorithm $algorithm): self
    {
        $this->hashAlgorithm = $algorithm;

        return $this;
    }

    public function setTemplateId(string $templateId): self
    {
        $this->templateId = $templateId;

        return $this;
    }

    public function build(): CertificateOptions
    {
        return new CertificateOptions(
            type: CertificateType::CLIENT_MTLS,
            validityDays: $this->validityDays,
            hashAlgorithm: $this->hashAlgorithm,
            keyUsage: $this->keyUsage,
            extendedKeyUsage: $this->extendedKeyUsage,
            subjectAlternativeNames: $this->sanEntries !== [] ? $this->sanEntries : null,
            isCa: false,
            customExtensions: $this->customExtensions,
            templateId: $this->templateId,
        );
    }
}
