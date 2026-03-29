<?php

declare(strict_types=1);

namespace CA\Ui\Http\Controllers;

use CA\Ui\Services\DashboardStatsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardStatsService $stats,
    ) {}

    public function index(): View
    {
        $totalCas = $this->stats->countCas();
        $certStats = $this->stats->countCertificates();
        $pendingCsrs = $this->stats->countPendingCsrs();
        $expiringCerts = $this->stats->getExpiringCertificates(30);
        $recentCerts = $this->stats->getRecentCertificates(10);
        $recentAudit = $this->stats->getRecentAuditLog(10);

        return view('ca::pages.dashboard', compact(
            'totalCas',
            'certStats',
            'pendingCsrs',
            'expiringCerts',
            'recentCerts',
            'recentAudit',
        ));
    }
}
