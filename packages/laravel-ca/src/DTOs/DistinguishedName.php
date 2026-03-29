<?php

declare(strict_types=1);

namespace CA\DTOs;

final readonly class DistinguishedName
{
    public function __construct(
        public ?string $commonName = null,
        public ?string $organization = null,
        public ?string $organizationalUnit = null,
        public ?string $country = null,
        public ?string $state = null,
        public ?string $locality = null,
        public ?string $emailAddress = null,
        public ?string $serialNumber = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'CN' => $this->commonName,
            'O' => $this->organization,
            'OU' => $this->organizationalUnit,
            'C' => $this->country,
            'ST' => $this->state,
            'L' => $this->locality,
            'emailAddress' => $this->emailAddress,
            'serialNumber' => $this->serialNumber,
        ], static fn(?string $value): bool => $value !== null);
    }

    public function toString(): string
    {
        $components = [];

        $mapping = [
            'CN' => $this->commonName,
            'O' => $this->organization,
            'OU' => $this->organizationalUnit,
            'C' => $this->country,
            'ST' => $this->state,
            'L' => $this->locality,
            'emailAddress' => $this->emailAddress,
            'serialNumber' => $this->serialNumber,
        ];

        foreach ($mapping as $key => $value) {
            if ($value !== null) {
                $escapedValue = self::escapeRfc2253Value($value);
                $components[] = "{$key}={$escapedValue}";
            }
        }

        return implode(',', $components);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            commonName: $data['CN'] ?? $data['commonName'] ?? $data['common_name'] ?? null,
            organization: $data['O'] ?? $data['organization'] ?? null,
            organizationalUnit: $data['OU'] ?? $data['organizationalUnit'] ?? $data['organizational_unit'] ?? null,
            country: $data['C'] ?? $data['country'] ?? null,
            state: $data['ST'] ?? $data['state'] ?? null,
            locality: $data['L'] ?? $data['locality'] ?? null,
            emailAddress: $data['emailAddress'] ?? $data['email_address'] ?? $data['email'] ?? null,
            serialNumber: $data['serialNumber'] ?? $data['serial_number'] ?? null,
        );
    }

    public static function fromString(string $dn): self
    {
        $components = self::parseRfc2253($dn);

        return self::fromArray($components);
    }

    private static function escapeRfc2253Value(string $value): string
    {
        $specialChars = ['\\', ',', '+', '"', '<', '>', ';'];

        $escaped = $value;
        foreach ($specialChars as $char) {
            $escaped = str_replace($char, '\\' . $char, $escaped);
        }

        if (str_starts_with($escaped, '#') || str_starts_with($escaped, ' ')) {
            $escaped = '\\' . $escaped;
        }

        if (str_ends_with($escaped, ' ')) {
            $escaped = substr($escaped, 0, -1) . '\\ ';
        }

        return $escaped;
    }

    private static function parseRfc2253(string $dn): array
    {
        $components = [];
        $parts = preg_split('/(?<!\\\\),/', $dn);

        if ($parts === false) {
            return $components;
        }

        foreach ($parts as $part) {
            $part = trim($part);
            $equalsPos = strpos($part, '=');

            if ($equalsPos === false) {
                continue;
            }

            $key = trim(substr($part, 0, $equalsPos));
            $value = trim(substr($part, $equalsPos + 1));

            $value = str_replace(
                ['\\,', '\\+', '\\"', '\\<', '\\>', '\\;', '\\\\'],
                [',', '+', '"', '<', '>', ';', '\\'],
                $value,
            );

            $components[$key] = $value;
        }

        return $components;
    }
}
