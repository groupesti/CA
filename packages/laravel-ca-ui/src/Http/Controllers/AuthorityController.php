<?php

declare(strict_types=1);

namespace CA\Ui\Http\Controllers;

use CA\Crl\Models\Crl;
use CA\Crt\Models\Certificate;
use CA\DTOs\DistinguishedName;
use CA\Models\CertificateStatus;
use CA\Models\KeyAlgorithm;
use CA\Models\CertificateAuthority;
use CA\Services\CaManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AuthorityController extends Controller
{
    public function __construct(
        private readonly CaManager $caManager,
    ) {}

    public function index(): View
    {
        $rootCas = CertificateAuthority::whereNull('parent_id')
            ->with('children')
            ->orderBy('created_at')
            ->get();

        return view('ca::pages.authorities.index', compact('rootCas'));
    }

    public function show(string $uuid): View
    {
        $authority = CertificateAuthority::where('id', $uuid)->firstOrFail();
        $authority->load(['parent', 'children']);

        $certificateCount = Certificate::where('ca_id', $authority->id)->count();
        $activeCertCount = Certificate::where('ca_id', $authority->id)
            ->where('status', CertificateStatus::ACTIVE)
            ->count();

        $latestCrl = Crl::where('ca_id', $authority->id)
            ->orderByDesc('crl_number')
            ->first();

        return view('ca::pages.authorities.show', compact(
            'authority',
            'certificateCount',
            'activeCertCount',
            'latestCrl',
        ));
    }

    public function create(): View
    {
        $parentCas = CertificateAuthority::where('status', CertificateStatus::ACTIVE)
            ->orderBy('created_at')
            ->get();

        $algorithms = KeyAlgorithm::cases();

        return view('ca::pages.authorities.create', compact('parentCas', 'algorithms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:root,intermediate',
            'parent_id' => 'nullable|required_if:type,intermediate|uuid',
            'common_name' => 'required|string|max:255',
            'organization' => 'nullable|string|max:255',
            'organizational_unit' => 'nullable|string|max:255',
            'country' => 'nullable|string|size:2',
            'state' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:255',
            'key_algorithm' => 'required|string',
            'validity_days' => 'required|integer|min:1|max:36500',
            'path_length' => 'nullable|integer|min:0',
        ]);

        $dn = new DistinguishedName(
            commonName: $validated['common_name'],
            organization: $validated['organization'] ?? null,
            organizationalUnit: $validated['organizational_unit'] ?? null,
            country: $validated['country'] ?? null,
            state: $validated['state'] ?? null,
            locality: $validated['locality'] ?? null,
        );

        $algorithm = KeyAlgorithm::from($validated['key_algorithm']);

        try {
            if ($validated['type'] === 'root') {
                $ca = $this->caManager->createRootCA(
                    dn: $dn,
                    algorithm: $algorithm,
                    validityDays: (int) $validated['validity_days'],
                    pathLength: isset($validated['path_length']) ? (int) $validated['path_length'] : null,
                );
            } else {
                $parent = CertificateAuthority::findOrFail($validated['parent_id']);
                $ca = $this->caManager->createIntermediateCA(
                    parent: $parent,
                    dn: $dn,
                    algorithm: $algorithm,
                    validityDays: (int) $validated['validity_days'],
                    pathLength: isset($validated['path_length']) ? (int) $validated['path_length'] : null,
                );
            }

            return redirect()
                ->route('ca.authorities.show', $ca->id)
                ->with('success', 'Certificate Authority created successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create CA: ' . $e->getMessage());
        }
    }
}
