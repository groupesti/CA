<?php

declare(strict_types=1);

namespace CA\Policy\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssuanceRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ca_id' => $this->ca_id,
            'policy_id' => $this->policy_id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'description' => $this->description,
            'rule_class' => $this->rule_class,
            'parameters' => $this->parameters,
            'priority' => $this->priority,
            'severity' => $this->severity,
            'action_on_failure' => $this->action_on_failure,
            'enabled' => $this->enabled,
            'applies_to_types' => $this->applies_to_types,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
