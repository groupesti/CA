<?php

declare(strict_types=1);

namespace CA\Oid\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OidResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'oid' => $this->oid,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'description' => $this->description,
            'category' => $this->category,
            'is_standard' => $this->is_standard,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
