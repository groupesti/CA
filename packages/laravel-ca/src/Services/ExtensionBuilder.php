<?php

declare(strict_types=1);

namespace CA\Services;

use CA\DTOs\ExtensionCollection;

final class ExtensionBuilder
{
    private readonly ExtensionCollection $extensions;

    public function __construct()
    {
        $this->extensions = new ExtensionCollection();
    }

    public function basicConstraints(bool $ca, ?int $pathLength = null, bool $critical = true): self
    {
        $value = ['ca' => $ca];

        if ($pathLength !== null) {
            $value['pathLenConstraint'] = $pathLength;
        }

        $this->extensions->add('2.5.29.19', $critical, $value);

        return $this;
    }

    public function keyUsage(array $usages, bool $critical = true): self
    {
        $this->extensions->add('2.5.29.15', $critical, $usages);

        return $this;
    }

    public function extendedKeyUsage(array $usages, bool $critical = false): self
    {
        $this->extensions->add('2.5.29.37', $critical, $usages);

        return $this;
    }

    public function subjectAlternativeName(array $names, bool $critical = false): self
    {
        $this->extensions->add('2.5.29.17', $critical, $names);

        return $this;
    }

    public function authorityInfoAccess(array $accessDescriptions, bool $critical = false): self
    {
        $this->extensions->add('1.3.6.1.5.5.7.1.1', $critical, $accessDescriptions);

        return $this;
    }

    public function crlDistributionPoints(array $distributionPoints, bool $critical = false): self
    {
        $this->extensions->add('2.5.29.31', $critical, $distributionPoints);

        return $this;
    }

    public function subjectKeyIdentifier(string $keyIdentifier, bool $critical = false): self
    {
        $this->extensions->add('2.5.29.14', $critical, $keyIdentifier);

        return $this;
    }

    public function authorityKeyIdentifier(string $keyIdentifier, bool $critical = false): self
    {
        $this->extensions->add('2.5.29.35', $critical, ['keyIdentifier' => $keyIdentifier]);

        return $this;
    }

    public function addMicrosoftTemplateExtension(string $templateName, bool $critical = false): self
    {
        $oid = config('ca.microsoft.oids.certificate_template_name', '1.3.6.1.4.1.311.20.2');

        $this->extensions->add($oid, $critical, $templateName);

        return $this;
    }

    public function addCustomExtension(string $oid, mixed $value, bool $critical = false): self
    {
        $this->extensions->add($oid, $critical, $value);

        return $this;
    }

    public function build(): ExtensionCollection
    {
        return $this->extensions;
    }
}
