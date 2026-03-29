<?php

declare(strict_types=1);

namespace CA\Ui\Http\Controllers;

use CA\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query();

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        $logs = $query->orderByDesc('performed_at')
            ->paginate(config('ca-ui.items_per_page', 25))
            ->withQueryString();

        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('ca::pages.audit.index', compact('logs', 'actions'));
    }
}
