<?php

declare(strict_types=1);

namespace CA\Ui\Services;

use CA\Crl\Models\Crl;
use CA\Crt\Models\Certificate;
use CA\Models\CsrStatus;
use CA\Csr\Models\Csr;
use CA\Models\CertificateStatus;
use CA\Models\AuditLog;
use CA\Models\CertificateAuthority;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class DashboardStatsService
{
    public function countCas(): int
    {
        return CertificateAuthority::count();
    }

    public function countCertificates(): array
    {
        $total = Certificate::count();
        $active = Certificate::where('status', CertificateStatus::ACTIVE)->count();
        $revoked = Certificate::where('status', CertificateStatus::REVOKED)->count();
        $expired = Certificate::where('status', CertificateStatus::EXPIRED)->count();

        return [
            'total' => $total,
            'active' => $active,
            'revoked' => $revoked,
            'expired' => $expired,
        ];
    }

    public function countPendingCsrs(): int
    {
        return Csr::where('status', CsrStatus::PENDING)->count();
    }

    public function getExpiringCertificates(int $days = 30, int $limit = 10): Collection
    {
        return Certificate::where('status', CertificateStatus::ACTIVE)
            ->where('not_after', '<=', Carbon::now()->addDays($days))
            ->where('not_after', '>', Carbon::now())
            ->orderBy('not_after')
            ->limit($limit)
            ->get();
    }

    public function getRecentCertificates(int $limit = 10): Collection
    {
        return Certificate::with('certificateAuthority')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getRecentAuditLog(int $limit = 10): Collection
    {
        return AuditLog::orderByDesc('performed_at')
            ->limit($limit)
            ->get();
    }

    public function getActiveCas(): Collection
    {
        return CertificateAuthority::where('status', CertificateStatus::ACTIVE)->get();
    }
}
