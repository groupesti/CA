<?php

declare(strict_types=1);

namespace CA\Csr\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \CA\Csr\Models\Csr
 */
class CsrResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'ca_id' => $this->ca_id,
            'subject_dn' => $this->subject_dn,
            'san' => $this->san,
            'status' => $this->status,
            'requested_by' => $this->requested_by,
            'approved_by' => $this->approved_by,
            'rejection_reason' => $this->rejection_reason,
            'template_id' => $this->template_id,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
