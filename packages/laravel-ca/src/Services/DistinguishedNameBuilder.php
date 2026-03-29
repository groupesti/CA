<?php

declare(strict_types=1);

namespace CA\Services;

use CA\DTOs\DistinguishedName;

final class DistinguishedNameBuilder
{
    private ?string $commonName = null;
    private ?string $organization = null;
    private ?string $organizationalUnit = null;
    private ?string $country = null;
    private ?string $state = null;
    private ?string $locality = null;
    private ?string $emailAddress = null;
    private ?string $serialNumber = null;

    public function commonName(string $value): self
    {
        $this->commonName = $value;
        return $this;
    }

    public function organization(string $value): self
    {
        $this->organization = $value;
        return $this;
    }

    public function organizationalUnit(string $value): self
    {
        $this->organizationalUnit = $value;
        return $this;
    }

    public function country(string $value): self
    {
        $this->country = $value;
        return $this;
    }

    public function state(string $value): self
    {
        $this->state = $value;
        return $this;
    }

    public function locality(string $value): self
    {
        $this->locality = $value;
        return $this;
    }

    public function emailAddress(string $value): self
    {
        $this->emailAddress = $value;
        return $this;
    }

    public function serialNumber(string $value): self
    {
        $this->serialNumber = $value;
        return $this;
    }

    public function build(): DistinguishedName
    {
        return new DistinguishedName(
            commonName: $this->commonName,
            organization: $this->organization,
            organizationalUnit: $this->organizationalUnit,
            country: $this->country,
            state: $this->state,
            locality: $this->locality,
            emailAddress: $this->emailAddress,
            serialNumber: $this->serialNumber,
        );
    }

    public static function parse(string $dn): DistinguishedName
    {
        return DistinguishedName::fromString($dn);
    }
}
