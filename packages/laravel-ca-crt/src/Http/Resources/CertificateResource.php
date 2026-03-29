<?php

declare(strict_types=1);

namespace CA\Crt\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \CA\Crt\Models\Certificate
 */
final class CertificateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'serial_number' => $this->serial_number,
            'type' => $this->type?->slug,
            'status' => $this->status?->slug,
            'subject_dn' => $this->subject_dn,
            'san' => $this->san,
            'fingerprint_sha256' => $this->fingerprint_sha256,
            'not_before' => $this->not_before?->toIso8601String(),
            'not_after' => $this->not_after?->toIso8601String(),
            'days_until_expiry' => $this->daysUntilExpiry(),
            'is_expired' => $this->isExpired(),
            'is_revoked' => $this->isRevoked(),
            'is_ca' => $this->isCa(),
            'revocation_reason' => $this->revocation_reason,
            'revoked_at' => $this->revoked_at?->toIso8601String(),
            'key_usage' => $this->key_usage,
            'extended_key_usage' => $this->extended_key_usage,
            'ms_template_oid' => $this->ms_template_oid,
            'ms_template_name' => $this->ms_template_name,
            'issuer' => $this->whenLoaded('issuerCertificate', fn() => [
                'uuid' => $this->issuerCertificate->uuid,
                'serial_number' => $this->issuerCertificate->serial_number,
                'subject_dn' => $this->issuerCertificate->subject_dn,
            ]),
            'certificate_authority' => $this->whenLoaded('certificateAuthority', fn() => [
                'id' => $this->certificateAuthority->id,
                'subject_dn' => $this->certificateAuthority->subject_dn,
            ]),
            'template' => $this->whenLoaded('template', fn() => [
                'id' => $this->template->id,
                'name' => $this->template->name,
            ]),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
