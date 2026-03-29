<?php

declare(strict_types=1);

namespace CA\Oid\DTOs;

use CA\Oid\Models\OidEntry;

final readonly class OidInfo
{
    public function __construct(
        public string $oid,
        public string $name,
        public ?string $shortName = null,
        public ?string $description = null,
        public string $category = 'custom',
        public bool $isStandard = false,
    ) {}

    public static function fromModel(OidEntry $model): self
    {
        return new self(
            oid: $model->oid,
            name: $model->name,
            shortName: $model->short_name,
            description: $model->description,
            category: $model->category,
            isStandard: $model->is_standard,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            oid: $data['oid'],
            name: $data['name'],
            shortName: $data['short_name'] ?? null,
            description: $data['description'] ?? null,
            category: $data['category'] ?? 'custom',
            isStandard: $data['is_standard'] ?? false,
        );
    }
}
