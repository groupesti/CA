<?php

declare(strict_types=1);

namespace CA\Key\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'algorithm' => $this->algorithm->slug,
            'parameters' => $this->parameters,
            'fingerprint_sha256' => $this->fingerprint_sha256,
            'status' => $this->status->slug,
            'usage' => $this->usage,
            'ca_id' => $this->ca_id,
            'tenant_id' => $this->tenant_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
