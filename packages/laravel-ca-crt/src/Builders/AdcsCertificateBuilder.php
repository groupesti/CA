<?php

declare(strict_types=1);

namespace CA\Crt\Builders;

use CA\Crt\Templates\MicrosoftTemplates\CodeSigningTemplate;
use CA\Crt\Templates\MicrosoftTemplates\ComputerTemplate;
use CA\Crt\Templates\MicrosoftTemplates\DomainControllerTemplate;
use CA\Crt\Templates\MicrosoftTemplates\SmimeTemplate;
use CA\Crt\Templates\MicrosoftTemplates\UserTemplate;
use CA\Crt\Templates\MicrosoftTemplates\WebServerTemplate;
use CA\DTOs\CertificateOptions;
use CA\Models\CertificateType;
use CA\Models\HashAlgorithm;

final class AdcsCertificateBuilder
{
    /** @var array<int, array<string, string>> */
    private array $sanEntries = [];

    private int $validityDays;

    private HashAlgorithm $hashAlgorithm;

    /** @var array<int, string> */
    private array $keyUsage = [];

    /** @var array<int, string> */
    private array $extendedKeyUsage = [];

    private CertificateType $type = CertificateType::CUSTOM;

    private bool $isCa = false;

    private ?int $pathLength = null;

    /** @var array<int, array{oid: string, critical: bool, value: mixed}> */
    private array $customExtensions = [];

    private ?string $templateId = null;

    private ?string $msTemplateOid = null;

    private ?string $msTemplateName = null;

    public function __construct()
    {
        $this->validityDays = (int) config('ca-crt.default_validity_days', 365);
        $this->hashAlgorithm = HashAlgorithm::from((string) config('ca-crt.default_hash', 'sha256'));
    }

    /**
     * Set Microsoft Certificate Template Information.
     */
    public function setMsTemplate(
        string $oid,
        string $name,
        int $majorVersion = 1,
        int $minorVersion = 0,
    ): self {
        $this->msTemplateOid = $oid;
        $this->msTemplateName = $name;

        // Certificate Template Information (1.3.6.1.4.1.311.21.7)
        $this->customExtensions[] = [
            'oid' => '1.3.6.1.4.1.311.21.7',
            'critical' => false,
            'value' => [
                'templateID' => $oid,
                'templateMajorVersion' => $majorVersion,
                'templateMinorVersion' => $minorVersion,
            ],
        ];

        // Certificate Template Name (1.3.6.1.4.1.311.20.2)
        $this->customExtensions[] = [
            'oid' => '1.3.6.1.4.1.311.20.2',
            'critical' => false,
            'value' => $name,
        ];

        // Application Policies (1.3.6.1.4.1.311.21.10)
        if ($this->extendedKeyUsage !== []) {
            $this->customExtensions[] = [
                'oid' => '1.3.6.1.4.1.311.21.10',
                'critical' => false,
                'value' => $this->extendedKeyUsage,
            ];
        }

        return $this;
    }

    /**
     * Configure for Microsoft Web Server template.
     */
    public function forWebServer(): self
    {
        $template = new WebServerTemplate();

        $this->type = CertificateType::SERVER_TLS;
        $this->keyUsage = $template->getKeyUsage();
        $this->extendedKeyUsage = $template->getExtendedKeyUsage();
        $this->validityDays = $template->getValidityDays();
        $this->isCa = false;

        return $this->setMsTemplate($template->getOid(), $template->getName());
    }

    /**
     * Configure for Microsoft User template.
     */
    public function forUser(): self
    {
        $template = new UserTemplate();

        $this->type = CertificateType::USER;
        $this->keyUsage = $template->getKeyUsage();
        $this->extendedKeyUsage = $template->getExtendedKeyUsage();
        $this->validityDays = $template->getValidityDays();
        $this->isCa = false;

        return $this->setMsTemplate($template->getOid(), $template->getName());
    }

    /**
     * Configure for Microsoft Computer template.
     */
    public function forComputer(): self
    {
        $template = new ComputerTemplate();

        $this->type = CertificateType::COMPUTER;
        $this->keyUsage = $template->getKeyUsage();
        $this->extendedKeyUsage = $template->getExtendedKeyUsage();
        $this->validityDays = $template->getValidityDays();
        $this->isCa = false;

        return $this->setMsTemplate($template->getOid(), $template->getName());
    }

    /**
     * Configure for Microsoft Domain Controller template.
     */
    public function forDomainController(): self
    {
        $template = new DomainControllerTemplate();

        $this->type = CertificateType::DOMAIN_CONTROLLER;
        $this->keyUsage = $template->getKeyUsage();
        $this->extendedKeyUsage = $template->getExtendedKeyUsage();
        $this->validityDays = $template->getValidityDays();
        $this->isCa = false;

        return $this->setMsTemplate($template->getOid(), $template->getName());
    }

    /**
     * Configure for Microsoft Code Signing template.
     */
    public function forCodeSigning(): self
    {
        $template = new CodeSigningTemplate();

        $this->type = CertificateType::CODE_SIGNING;
        $this->keyUsage = $template->getKeyUsage();
        $this->extendedKeyUsage = $template->getExtendedKeyUsage();
        $this->validityDays = $template->getValidityDays();
        $this->isCa = false;

        return $this->setMsTemplate($template->getOid(), $template->getName());
    }

    /**
     * Configure for Microsoft S/MIME template.
     */
    public function forSmime(): self
    {
        $template = new SmimeTemplate();

        $this->type = CertificateType::SMIME;
        $this->keyUsage = $template->getKeyUsage();
        $this->extendedKeyUsage = $template->getExtendedKeyUsage();
        $this->validityDays = $template->getValidityDays();
        $this->isCa = false;

        return $this->setMsTemplate($template->getOid(), $template->getName());
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
            type: $this->type,
            validityDays: $this->validityDays,
            hashAlgorithm: $this->hashAlgorithm,
            keyUsage: $this->keyUsage,
            extendedKeyUsage: $this->extendedKeyUsage,
            subjectAlternativeNames: $this->sanEntries !== [] ? $this->sanEntries : null,
            isCa: $this->isCa,
            pathLength: $this->pathLength,
            customExtensions: $this->customExtensions,
            templateId: $this->templateId,
        );
    }
}
