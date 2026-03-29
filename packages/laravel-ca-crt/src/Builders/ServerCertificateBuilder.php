<?php

declare(strict_types=1);

namespace CA\Crt\Builders;

use CA\DTOs\CertificateOptions;
use CA\Models\CertificateType;
use CA\Models\HashAlgorithm;

final class ServerCertificateBuilder
{
    /** @var array<int, array<string, string>> */
    private array $sanEntries = [];

    private ?string $organization = null;

    private int $validityDays;

    private HashAlgorithm $hashAlgorithm;

    /** @var array<int, string> */
    private array $keyUsage = ['digitalSignature', 'keyEncipherment'];

    /** @var array<int, string> */
    private array $extendedKeyUsage = ['serverAuth'];

    /** @var array<int, array{oid: string, critical: bool, value: mixed}> */
    private array $customExtensions = [];

    private ?string $templateId = null;

    public function __construct()
    {
        $this->validityDays = (int) config('ca-crt.default_validity_days', 365);
        $this->hashAlgorithm = HashAlgorithm::from((string) config('ca-crt.default_hash', 'sha256'));
    }

    public function addDnsName(string $dnsName): self
    {
        $this->sanEntries[] = ['dNSName' => $dnsName];

        return $this;
    }

    public function addIpAddress(string $ip): self
    {
        $this->sanEntries[] = ['iPAddress' => $ip];

        return $this;
    }

    public function addWildcard(string $domain): self
    {
        $this->sanEntries[] = ['dNSName' => '*.' . ltrim($domain, '*.')];

        return $this;
    }

    public function setOrganization(string $organization): self
    {
        $this->organization = $organization;

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

    public function addCustomExtension(string $oid, mixed $value, bool $critical = false): self
    {
        $this->customExtensions[] = ['oid' => $oid, 'critical' => $critical, 'value' => $value];

        return $this;
    }

    public function build(): CertificateOptions
    {
        return new CertificateOptions(
            type: CertificateType::SERVER_TLS,
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
