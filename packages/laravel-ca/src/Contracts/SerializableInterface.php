<?php

declare(strict_types=1);

namespace CA\Contracts;

interface SerializableInterface
{
    public function toPem(): string;

    public function toDer(): string;

    public function toArray(): array;
}
