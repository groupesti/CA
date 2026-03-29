<?php

declare(strict_types=1);

namespace CA\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'parent_id' => $this->parent_id,
            'type' => $this->type->slug,
            'status' => $this->status->slug,
            'subject_dn' => $this->subject_dn,
            'serial_number' => $this->serial_number,
            'key_algorithm' => $this->key_algorithm,
            'hash_algorithm' => $this->hash_algorithm,
            'path_length' => $this->path_length,
            'is_root' => $this->isRoot(),
            'chain_depth' => $this->getChainDepth(),
            'not_before' => $this->not_before?->toIso8601String(),
            'not_after' => $this->not_after?->toIso8601String(),
            'metadata' => $this->metadata,
            'children_count' => $this->whenCounted('children'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
