<?php

declare(strict_types=1);

namespace CA\Key\Http\Controllers;

use CA\Models\ExportFormat;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Models\KeyStatus;
use CA\Key\Http\Requests\GenerateKeyRequest;
use CA\Key\Http\Resources\KeyResource;
use CA\Key\Models\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class KeyController extends Controller
{
    public function __construct(
        private readonly KeyManagerInterface $keyManager,
    ) {}

    /**
     * List all keys (never exposes private key data).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Key::query();

        if ($request->has('algorithm')) {
            $query->where('algorithm', $request->input('algorithm'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('ca_id')) {
            $query->where('ca_id', $request->input('ca_id'));
        }

        if ($request->has('usage')) {
            $query->where('usage', $request->input('usage'));
        }

        $keys = $query->latest()->paginate(
            (int) $request->input('per_page', 15),
        );

        return KeyResource::collection($keys);
    }

    /**
     * Generate a new key.
     */
    public function store(GenerateKeyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $algorithm = \CA\Enums\KeyAlgorithm::from($validated['algorithm']);

        $key = $this->keyManager->generate(
            algorithm: $algorithm,
            params: [
                'ca_id' => $validated['ca_id'] ?? null,
                'usage' => $validated['usage'] ?? 'certificate',
                ...(isset($validated['parameters']) ? $validated['parameters'] : []),
            ],
            tenantId: $validated['tenant_id'] ?? null,
        );

        return (new KeyResource($key))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show key metadata (never exposes private key).
     */
    public function show(string $uuid): KeyResource
    {
        $key = Key::where('uuid', $uuid)->firstOrFail();

        return new KeyResource($key);
    }

    /**
     * Destroy a key.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $key = Key::where('uuid', $uuid)->firstOrFail();

        $this->keyManager->destroy($key);

        return response()->json(['message' => 'Key destroyed.'], 200);
    }

    /**
     * Export a key in the specified format.
     */
    public function export(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'format' => ['required', 'string', 'in:pem,der'],
            'passphrase' => ['nullable', 'string', 'min:8'],
        ]);

        $key = Key::where('uuid', $uuid)->firstOrFail();

        $format = ExportFormat::from($request->input('format'));
        $passphrase = $request->input('passphrase');

        $exported = $this->keyManager->export($key, $format, $passphrase);

        if ($format === ExportFormat::DER) {
            return response()->json([
                'data' => base64_encode($exported),
                'encoding' => 'base64',
                'format' => $format->slug,
            ]);
        }

        return response()->json([
            'data' => $exported,
            'format' => $format->slug,
        ]);
    }

    /**
     * Rotate a key.
     */
    public function rotate(string $uuid): JsonResponse
    {
        $key = Key::where('uuid', $uuid)->firstOrFail();

        $newKey = $this->keyManager->rotate($key);

        return (new KeyResource($newKey))
            ->response()
            ->setStatusCode(201);
    }
}
