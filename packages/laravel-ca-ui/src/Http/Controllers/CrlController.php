<?php

declare(strict_types=1);

namespace CA\Ui\Http\Controllers;

use CA\Crl\Models\Crl;
use CA\Crl\Services\CrlManager;
use CA\Models\CertificateAuthority;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

final class CrlController extends Controller
{
    public function __construct(
        private readonly CrlManager $crlManager,
    ) {}

    public function index(): View
    {
        $authorities = CertificateAuthority::with(['children'])->get();

        $crls = Crl::with('certificateAuthority')
            ->orderByDesc('this_update')
            ->paginate(config('ca-ui.items_per_page', 25));

        return view('ca::pages.crls.index', compact('crls', 'authorities'));
    }

    public function generate(string $ca_uuid): RedirectResponse
    {
        $ca = CertificateAuthority::where('id', $ca_uuid)->firstOrFail();

        try {
            $this->crlManager->generate($ca);

            return redirect()
                ->route('ca.crls.index')
                ->with('success', 'CRL generated successfully for CA: ' . ($ca->subject_dn['CN'] ?? $ca->id) . '.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to generate CRL: ' . $e->getMessage());
        }
    }
}
