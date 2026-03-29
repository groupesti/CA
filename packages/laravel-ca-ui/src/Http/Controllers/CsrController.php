<?php

declare(strict_types=1);

namespace CA\Ui\Http\Controllers;

use CA\Models\CsrStatus;
use CA\Csr\Models\Csr;
use CA\Csr\Services\CsrManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class CsrController extends Controller
{
    public function __construct(
        private readonly CsrManager $csrManager,
    ) {}

    public function index(Request $request): View
    {
        $query = Csr::with('certificateAuthority');

        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where('status', $status);
        }

        $csrs = $query->orderByDesc('created_at')
            ->paginate(config('ca-ui.items_per_page', 25))
            ->withQueryString();

        $statuses = [CsrStatus::PENDING, CsrStatus::APPROVED, CsrStatus::REJECTED, CsrStatus::SIGNED];

        return view('ca::pages.csrs.index', compact('csrs', 'statuses'));
    }

    public function show(string $uuid): View
    {
        $csr = Csr::where('uuid', $uuid)
            ->with(['certificateAuthority', 'key', 'template'])
            ->firstOrFail();

        return view('ca::pages.csrs.show', compact('csr'));
    }

    public function approve(string $uuid): RedirectResponse
    {
        $csr = Csr::where('uuid', $uuid)->firstOrFail();

        try {
            $this->csrManager->approve($csr);

            return redirect()
                ->route('ca.csrs.show', $uuid)
                ->with('success', 'CSR approved successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to approve CSR: ' . $e->getMessage());
        }
    }

    public function reject(string $uuid, Request $request): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $csr = Csr::where('uuid', $uuid)->firstOrFail();

        try {
            $this->csrManager->reject($csr, $request->input('reason'));

            return redirect()
                ->route('ca.csrs.show', $uuid)
                ->with('success', 'CSR rejected.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to reject CSR: ' . $e->getMessage());
        }
    }
}
