<?php

declare(strict_types=1);

namespace CA\Oid\Http\Controllers;

use CA\Oid\Http\Requests\RegisterOidRequest;
use CA\Oid\Http\Resources\OidResource;
use CA\Oid\Models\OidEntry;
use CA\Oid\Services\OidRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class OidController extends Controller
{
    public function __construct(
        private readonly OidRegistry $registry,
    ) {}

    /**
     * List OIDs with optional search and category filter.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = OidEntry::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('category')) {
            $query->byCategory($request->input('category'));
        }

        return OidResource::collection(
            $query->orderBy('oid')->paginate($request->integer('per_page', 50))
        );
    }

    /**
     * Show a single OID entry.
     */
    public function show(string $oid): JsonResponse
    {
        $info = $this->registry->resolve($oid);

        if ($info === null) {
            return response()->json(['message' => 'OID not found.'], 404);
        }

        return response()->json([
            'data' => [
                'oid' => $info->oid,
                'name' => $info->name,
                'short_name' => $info->shortName,
                'description' => $info->description,
                'category' => $info->category,
                'is_standard' => $info->isStandard,
            ],
        ]);
    }

    /**
     * Register a custom OID.
     */
    public function store(RegisterOidRequest $request): OidResource
    {
        $entry = $this->registry->register(
            oid: $request->validated('oid'),
            name: $request->validated('name'),
            description: $request->validated('description', ''),
            category: $request->validated('category'),
        );

        return new OidResource($entry);
    }

    /**
     * Unregister a custom OID.
     */
    public function destroy(string $oid): JsonResponse
    {
        $removed = $this->registry->unregister($oid);

        if (! $removed) {
            return response()->json([
                'message' => 'OID not found or is a standard OID and cannot be removed.',
            ], 404);
        }

        return response()->json(['message' => 'OID unregistered successfully.']);
    }

    /**
     * Search OIDs by query string.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $results = $this->registry->search($query);

        return response()->json([
            'data' => $results->map(fn ($info) => [
                'oid' => $info->oid,
                'name' => $info->name,
                'short_name' => $info->shortName,
                'description' => $info->description,
                'category' => $info->category,
                'is_standard' => $info->isStandard,
            ])->values(),
        ]);
    }

    /**
     * List all available categories.
     */
    public function categories(): JsonResponse
    {
        $categories = $this->registry->getCategories();

        return response()->json(['data' => $categories]);
    }
}
