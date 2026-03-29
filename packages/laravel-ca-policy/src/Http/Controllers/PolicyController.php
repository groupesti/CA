<?php

declare(strict_types=1);

namespace CA\Policy\Http\Controllers;

use CA\DTOs\CertificateOptions;
use CA\Models\CertificateAuthority;
use CA\Policy\Contracts\PolicyEngineInterface;
use CA\Policy\Http\Requests\CreatePolicyRequest;
use CA\Policy\Http\Resources\PolicyResource;
use CA\Policy\Models\CertificatePolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class PolicyController extends Controller
{
    public function __construct(
        private readonly PolicyEngineInterface $policyEngine,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CertificatePolicy::query();

        if ($request->has('ca_id')) {
            $query->where('ca_id', $request->input('ca_id'));
        }

        $policies = $query->with(['constraints', 'rules'])->paginate(
            $request->integer('per_page', 15),
        );

        return PolicyResource::collection($policies);
    }

    public function store(CreatePolicyRequest $request): PolicyResource
    {
        $policy = CertificatePolicy::create($request->validated());

        return new PolicyResource($policy->load(['constraints', 'rules']));
    }

    public function show(string $id): PolicyResource
    {
        $policy = CertificatePolicy::with(['constraints', 'rules', 'policyConstraints'])->findOrFail($id);

        return new PolicyResource($policy);
    }

    public function update(CreatePolicyRequest $request, string $id): PolicyResource
    {
        $policy = CertificatePolicy::findOrFail($id);
        $policy->update($request->validated());

        return new PolicyResource($policy->load(['constraints', 'rules']));
    }

    public function destroy(string $id): JsonResponse
    {
        $policy = CertificatePolicy::findOrFail($id);
        $policy->delete();

        return response()->json(['message' => 'Policy deleted.'], 200);
    }

    public function evaluate(Request $request): JsonResponse
    {
        $request->validate([
            'ca_id' => ['required', 'uuid', 'exists:certificate_authorities,id'],
            'options' => ['required', 'array'],
            'options.type' => ['required', 'string'],
            'options.validity_days' => ['required', 'integer', 'min:1'],
        ]);

        $ca = CertificateAuthority::findOrFail($request->input('ca_id'));
        $options = CertificateOptions::fromArray($request->input('options'));

        $result = $this->policyEngine->evaluate($ca, $options);

        return response()->json([
            'allowed' => $result->isAllowed(),
            'action' => $result->action,
            'violations' => $result->getViolations(),
            'warnings' => $result->warnings,
        ]);
    }
}
