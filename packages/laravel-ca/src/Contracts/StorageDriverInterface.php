<?php

declare(strict_types=1);

namespace CA\Contracts;

interface StorageDriverInterface
{
    public function store(string $identifier, string $content, string $format): string;

    public function retrieve(string $identifier, string $format): ?string;

    public function exists(string $identifier, string $format): bool;

    public function delete(string $identifier, string $format): bool;

    public function getPath(string $identifier, string $format): string;
}
