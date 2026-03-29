<?php

declare(strict_types=1);

namespace CA\Csr\Http\Controllers;

use CA\Csr\Contracts\CsrManagerInterface;
use CA\Csr\Http\Requests\CreateCsrRequest;
use CA\Csr\Http\Requests\ImportCsrRequest;
use CA\Csr\Http\Resources\CsrResource;
use CA\Csr\Models\Csr;
use CA\DTOs\DistinguishedName;
use CA\Exceptions\CsrException;
use CA\Key\Models\Key;
use CA\Models\CertificateTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class CsrController extends Controller
{
    public function __construct(
        private readonly CsrManagerInterface $manager,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Csr::query();

        if ($request->has('ca_id')) {
            $query->where('ca_id', $request->input('ca_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        return CsrResource::collection(
            $query->latest()->paginate($request->integer('per_page', 15)),
        );
    }

    public function store(CreateCsrRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $key = Key::findOrFail($validated['key_id']);
        $dn = DistinguishedName::fromArray($validated['subject']);

        $extensions = [];
        foreach ($validated['san'] ?? [] as $san) {
            $extensions[] = [
                'type' => $san['type'],
                'value' => $san['value'],
            ];
        }

        $template = isset($validated['template_id'])
            ? CertificateTemplate::findOrFail($validated['template_id'])
            : null;

        $csr = $this->manager->create($dn, $key, $extensions, $template);

        return (new CsrResource($csr))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $uuid): CsrResource
    {
        $csr = $this->manager->findByUuid($uuid);

        if ($csr === null) {
            abort(404, 'CSR not found.');
        }

        return new CsrResource($csr);
    }

    public function import(ImportCsrRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $csr = $this->manager->import($validated['csr_pem']);

        if (isset($validated['ca_id'])) {
            $csr->update(['ca_id' => $validated['ca_id']]);
            $csr->refresh();
        }

        return (new CsrResource($csr))
            ->response()
            ->setStatusCode(201);
    }

    public function approve(string $uuid, Request $request): JsonResponse
    {
        $csr = $this->manager->findByUuid($uuid);

        if ($csr === null) {
            abort(404, 'CSR not found.');
        }

        try {
            $csr = $this->manager->approve($csr, $request->input('approved_by'));
        } catch (CsrException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return (new CsrResource($csr))
            ->response()
            ->setStatusCode(200);
    }

    public function reject(string $uuid, Request $request): JsonResponse
    {
        $csr = $this->manager->findByUuid($uuid);

        if ($csr === null) {
            abort(404, 'CSR not found.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $csr = $this->manager->reject($csr, $request->input('reason'), $request->input('rejected_by'));
        } catch (CsrException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return (new CsrResource($csr))
            ->response()
            ->setStatusCode(200);
    }
}
