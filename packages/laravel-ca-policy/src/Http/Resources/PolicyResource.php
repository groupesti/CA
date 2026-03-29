<?php

declare(strict_types=1);

namespace CA\Policy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ca_id' => $this->ca_id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'policy_oid' => $this->policy_oid,
            'cps_uri' => $this->cps_uri,
            'description' => $this->description,
            'is_default' => $this->is_default,
            'enabled' => $this->enabled,
            'metadata' => $this->metadata,
            'constraints' => NameConstraintResource::collection($this->whenLoaded('constraints')),
            'rules' => IssuanceRuleResource::collection($this->whenLoaded('rules')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
