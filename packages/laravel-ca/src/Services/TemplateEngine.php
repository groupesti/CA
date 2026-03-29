<?php

declare(strict_types=1);

namespace CA\Services;

use CA\Models\CertificateTemplate;
use Illuminate\Support\Collection;

final class TemplateEngine
{
    public function getTemplate(string $name): ?CertificateTemplate
    {
        return CertificateTemplate::query()
            ->where('name', $name)
            ->where('is_active', true)
            ->first();
    }

    public function listTemplates(?string $caId = null): Collection
    {
        $query = CertificateTemplate::query()
            ->where('is_active', true);

        if ($caId !== null) {
            $query->where(function ($q) use ($caId): void {
                $q->where('certificate_authority_id', $caId)
                    ->orWhereNull('certificate_authority_id');
            });
        }

        return $query->orderBy('name')->get();
    }

    public function resolveTemplate(string $nameOrId): ?CertificateTemplate
    {
        return CertificateTemplate::query()
            ->where('is_active', true)
            ->where(function ($q) use ($nameOrId): void {
                $q->where('id', $nameOrId)
                    ->orWhere('name', $nameOrId)
                    ->orWhere('slug', $nameOrId);
            })
            ->first();
    }

    public function validateTemplateForCa(CertificateTemplate $template, string $caId): bool
    {
        if ($template->certificate_authority_id === null) {
            return true;
        }

        return $template->certificate_authority_id === $caId;
    }
}
