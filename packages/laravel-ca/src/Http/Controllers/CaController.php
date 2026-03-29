<?php

declare(strict_types=1);

namespace CA\Http\Controllers;

use CA\DTOs\DistinguishedName;
use CA\Models\KeyAlgorithm;
use CA\Http\Requests\CreateCaRequest;
use CA\Http\Resources\CaResource;
use CA\Models\CertificateAuthority;
use CA\Services\CaManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class CaController extends Controller
{
    public function __construct(
        private readonly CaManager $caManager,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CertificateAuthority::query();

        if ($request->has('tenant_id')) {
            $query->forTenant($request->input('tenant_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->boolean('root_only')) {
            $query->root();
        }

        $cas = $query->orderBy('created_at', 'desc')->paginate(
            $request->integer('per_page', 15),
        );

        return CaResource::collection($cas);
    }

    public function store(CreateCaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dn = DistinguishedName::fromArray($validated['subject_dn']);
        $algorithm = KeyAlgorithm::from($validated['key_algorithm']);

        if (isset($validated['parent_id'])) {
            $parent = CertificateAuthority::findOrFail($validated['parent_id']);

            $ca = $this->caManager->createIntermediateCA(
                parent: $parent,
                dn: $dn,
                algorithm: $algorithm,
                keyParams: $validated['key_params'] ?? [],
                validityDays: $validated['validity_days'] ?? (int) config('ca.defaults.validity.intermediate_ca', 1825),
                pathLength: $validated['path_length'] ?? null,
            );
        } else {
            $ca = $this->caManager->createRootCA(
                dn: $dn,
                algorithm: $algorithm,
                keyParams: $validated['key_params'] ?? [],
                validityDays: $validated['validity_days'] ?? (int) config('ca.defaults.validity.root_ca', 3650),
                tenantId: $validated['tenant_id'] ?? null,
                pathLength: $validated['path_length'] ?? null,
            );
        }

        return (new CaResource($ca))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $id): CaResource
    {
        $ca = CertificateAuthority::findOrFail($id);

        return new CaResource($ca);
    }

    public function update(Request $request, string $id): CaResource
    {
        $ca = CertificateAuthority::findOrFail($id);

        $validated = $request->validate([
            'metadata' => ['sometimes', 'array'],
        ]);

        if (isset($validated['metadata'])) {
            $ca->metadata = array_merge($ca->metadata ?? [], $validated['metadata']);
        }

        $ca->save();

        return new CaResource($ca);
    }

    public function destroy(string $id): JsonResponse
    {
        $ca = CertificateAuthority::findOrFail($id);

        if ($ca->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a CA that has child CAs. Revoke or delete children first.',
            ], 409);
        }

        $ca->delete();

        return response()->json(null, 204);
    }

    public function hierarchy(string $id): JsonResponse
    {
        $ca = CertificateAuthority::findOrFail($id);

        $hierarchy = $this->caManager->getHierarchy($ca);

        return response()->json([
            'data' => $hierarchy,
        ]);
    }
}
