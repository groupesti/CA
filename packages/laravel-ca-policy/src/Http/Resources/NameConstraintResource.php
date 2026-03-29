<?php

declare(strict_types=1);

namespace CA\Policy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NameConstraintResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'policy_id' => $this->policy_id,
            'ca_id' => $this->ca_id,
            'type' => $this->type,
            'name_type' => $this->name_type,
            'value' => $this->value,
            'min_subtree' => $this->min_subtree,
            'max_subtree' => $this->max_subtree,
            'enabled' => $this->enabled,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
