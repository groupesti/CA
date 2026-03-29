<?php

declare(strict_types=1);

namespace CA\Ui\Http\Controllers;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Models\Certificate;
use CA\Models\CertificateStatus;
use CA\Models\CertificateType;
use CA\Models\RevocationReason;
use CA\Models\CertificateAuthority;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

final class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateManagerInterface $certificateManager,
    ) {}

    public function index(Request $request): View
    {
        $query = Certificate::with('certificateAuthority');

        if ($request->filled('type')) {
            $type = CertificateType::tryFrom($request->input('type'));
            if ($type !== null) {
                $query->where('type', $type);
            }
        }

        if ($request->filled('status')) {
            $status = CertificateStatus::tryFrom($request->input('status'));
            if ($status !== null) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('ca_id')) {
            $query->where('ca_id', $request->input('ca_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('subject_dn', 'like', "%{$search}%")
                    ->orWhere('fingerprint_sha256', 'like', "%{$search}%");
            });
        }

        $certificates = $query->orderByDesc('created_at')
            ->paginate(config('ca-ui.items_per_page', 25))
            ->withQueryString();

        $authorities = CertificateAuthority::orderBy('created_at')->get();
        $types = CertificateType::cases();
        $statuses = CertificateStatus::cases();

        return view('ca::pages.certificates.index', compact(
            'certificates',
            'authorities',
            'types',
            'statuses',
        ));
    }

    public function show(string $uuid): View
    {
        $certificate = Certificate::where('uuid', $uuid)
            ->with(['certificateAuthority', 'key', 'csr', 'issuerCertificate', 'chains.parentCertificate'])
            ->firstOrFail();

        $revocationReasons = RevocationReason::cases();

        return view('ca::pages.certificates.show', compact('certificate', 'revocationReasons'));
    }

    public function revoke(string $uuid, Request $request): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|integer',
        ]);

        $certificate = Certificate::where('uuid', $uuid)->firstOrFail();
        $reason = RevocationReason::from((int) $request->input('reason'));

        try {
            $this->certificateManager->revoke($certificate, $reason);

            return redirect()
                ->route('ca.certificates.show', $uuid)
                ->with('success', 'Certificate revoked successfully.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to revoke certificate: ' . $e->getMessage());
        }
    }

    public function export(string $uuid, Request $request): Response
    {
        $certificate = Certificate::where('uuid', $uuid)->firstOrFail();
        $format = $request->input('format', 'pem');

        if ($format === 'der' && $certificate->certificate_der !== null) {
            return response($certificate->certificate_der, 200, [
                'Content-Type' => 'application/x-x509-ca-cert',
                'Content-Disposition' => "attachment; filename=\"{$certificate->serial_number}.der\"",
            ]);
        }

        $pem = $certificate->certificate_pem ?? '';

        return response($pem, 200, [
            'Content-Type' => 'application/x-pem-file',
            'Content-Disposition' => "attachment; filename=\"{$certificate->serial_number}.pem\"",
        ]);
    }
}
