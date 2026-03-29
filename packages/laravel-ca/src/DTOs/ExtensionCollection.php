<?php

declare(strict_types=1);

namespace CA\DTOs;

final class ExtensionCollection
{
    /** @var array<string, array{oid: string, critical: bool, value: mixed}> */
    private array $extensions = [];

    public function add(string $oid, bool $critical, mixed $value): self
    {
        $this->extensions[$oid] = [
            'oid' => $oid,
            'critical' => $critical,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * @return array{oid: string, critical: bool, value: mixed}|null
     */
    public function get(string $oid): ?array
    {
        return $this->extensions[$oid] ?? null;
    }

    public function has(string $oid): bool
    {
        return isset($this->extensions[$oid]);
    }

    /**
     * @return array<string, array{oid: string, critical: bool, value: mixed}>
     */
    public function all(): array
    {
        return $this->extensions;
    }

    public function toArray(): array
    {
        return array_values($this->extensions);
    }
}
