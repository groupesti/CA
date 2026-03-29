<?php

declare(strict_types=1);

namespace CA\Crt\Http\Controllers;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Contracts\CertificateValidatorInterface;
use CA\Crt\Http\Requests\IssueCertificateRequest;
use CA\Crt\Http\Requests\RenewCertificateRequest;
use CA\Crt\Http\Requests\RevokeCertificateRequest;
use CA\Crt\Http\Resources\CertificateResource;
use CA\Crt\Models\Certificate;
use CA\Csr\Models\Csr;
use CA\DTOs\CertificateOptions;
use CA\Models\CertificateStatus;
use CA\Models\CertificateType;
use CA\Models\ExportFormat;
use CA\Models\RevocationReason;
use CA\Models\CertificateAuthority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

final class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateManagerInterface $manager,
        private readonly CertificateValidatorInterface $validator,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Certificate::query()
            ->with(['issuerCertificate', 'certificateAuthority', 'template']);

        if ($request->has('ca_id')) {
            $query->where('ca_id', $request->input('ca_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->input('tenant_id'));
        }

        return CertificateResource::collection(
            $query->orderByDesc('created_at')->paginate(
                (int) $request->input('per_page', 25),
            ),
        );
    }

    public function store(IssueCertificateRequest $request): CertificateResource
    {
        $validated = $request->validated();

        $ca = CertificateAuthority::findOrFail($validated['ca_id']);

        $options = CertificateOptions::fromArray([
            'type' => $validated['type'],
            'validity_days' => $validated['validity_days']
                ?? (int) config('ca-crt.default_validity_days', 365),
            'hash_algorithm' => $validated['hash_algorithm'] ?? null,
            'key_usage' => $validated['key_usage'] ?? [],
            'extended_key_usage' => $validated['extended_key_usage'] ?? [],
            'subject_alternative_names' => $validated['san'] ?? null,
            'template_id' => $validated['template_id'] ?? null,
        ]);

        $csr = Csr::findOrFail($validated['csr_id']);
        $certificate = $this->manager->issueFromCsr($ca, $csr, $options);

        $certificate->load(['issuerCertificate', 'certificateAuthority', 'template']);

        return new CertificateResource($certificate);
    }

    public function show(string $uuid): CertificateResource
    {
        $certificate = Certificate::query()
            ->where('uuid', $uuid)
            ->with(['issuerCertificate', 'certificateAuthority', 'template'])
            ->firstOrFail();

        return new CertificateResource($certificate);
    }

    public function revoke(string $uuid, RevokeCertificateRequest $request): CertificateResource
    {
        $certificate = Certificate::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $reason = RevocationReason::from((int) $request->validated('reason'));

        $certificate = $this->manager->revoke($certificate, $reason);
        $certificate->load(['issuerCertificate', 'certificateAuthority']);

        return new CertificateResource($certificate);
    }

    public function renew(string $uuid, RenewCertificateRequest $request): CertificateResource
    {
        $certificate = Certificate::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $validityDays = $request->validated('validity_days')
            ? (int) $request->validated('validity_days')
            : null;

        $newCertificate = $this->manager->renew($certificate, $validityDays);
        $newCertificate->load(['issuerCertificate', 'certificateAuthority', 'template']);

        return new CertificateResource($newCertificate);
    }

    public function chain(string $uuid): JsonResponse
    {
        $certificate = Certificate::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $chain = $this->manager->getChain($certificate);

        return response()->json([
            'data' => array_map(
                fn(Certificate $cert) => [
                    'uuid' => $cert->uuid,
                    'serial_number' => $cert->serial_number,
                    'subject_dn' => $cert->subject_dn,
                    'type' => $cert->type?->slug,
                    'not_before' => $cert->not_before?->toIso8601String(),
                    'not_after' => $cert->not_after?->toIso8601String(),
                ],
                $chain,
            ),
        ]);
    }

    public function export(string $uuid, Request $request): JsonResponse
    {
        $certificate = Certificate::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $formatValue = $request->input('format', 'pem');
        $format = ExportFormat::from($formatValue);

        $exported = $this->manager->export($certificate, $format);

        return response()->json([
            'data' => [
                'format' => $format->slug,
                'certificate' => base64_encode($exported),
            ],
        ]);
    }

    public function verify(string $uuid): JsonResponse
    {
        $certificate = Certificate::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $isValid = $this->validator->validate($certificate);

        return response()->json([
            'data' => [
                'valid' => $isValid,
                'errors' => $this->validator->getErrors(),
            ],
        ]);
    }

    public function expiring(Request $request): AnonymousResourceCollection
    {
        $days = (int) $request->input('days', 30);

        $certificates = $this->manager->getExpiring($days);

        return CertificateResource::collection($certificates);
    }
}
