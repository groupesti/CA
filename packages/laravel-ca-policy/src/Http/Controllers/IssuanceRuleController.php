<?php

declare(strict_types=1);

namespace CA\Policy\Http\Controllers;

use CA\Policy\Events\IssuanceRuleCreated;
use CA\Policy\Http\Requests\CreateIssuanceRuleRequest;
use CA\Policy\Http\Resources\IssuanceRuleResource;
use CA\Policy\Models\IssuanceRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class IssuanceRuleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = IssuanceRule::query();

        if ($request->has('ca_id')) {
            $query->where('ca_id', $request->input('ca_id'));
        }

        $rules = $query->byPriority()->paginate($request->integer('per_page', 15));

        return IssuanceRuleResource::collection($rules);
    }

    public function store(CreateIssuanceRuleRequest $request): IssuanceRuleResource
    {
        $rule = IssuanceRule::create($request->validated());

        event(new IssuanceRuleCreated($rule));

        return new IssuanceRuleResource($rule);
    }

    public function show(string $id): IssuanceRuleResource
    {
        $rule = IssuanceRule::findOrFail($id);

        return new IssuanceRuleResource($rule);
    }

    public function update(CreateIssuanceRuleRequest $request, string $id): IssuanceRuleResource
    {
        $rule = IssuanceRule::findOrFail($id);
        $rule->update($request->validated());

        return new IssuanceRuleResource($rule);
    }

    public function destroy(string $id): JsonResponse
    {
        $rule = IssuanceRule::findOrFail($id);
        $rule->delete();

        return response()->json(['message' => 'Issuance rule deleted.'], 200);
    }

    public function enable(string $id): IssuanceRuleResource
    {
        $rule = IssuanceRule::findOrFail($id);
        $rule->update(['enabled' => true]);

        return new IssuanceRuleResource($rule);
    }

    public function disable(string $id): IssuanceRuleResource
    {
        $rule = IssuanceRule::findOrFail($id);
        $rule->update(['enabled' => false]);

        return new IssuanceRuleResource($rule);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'rules' => ['required', 'array'],
            'rules.*.id' => ['required', 'uuid', 'exists:ca_issuance_rules,id'],
            'rules.*.priority' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->input('rules') as $ruleData) {
            IssuanceRule::where('id', $ruleData['id'])->update([
                'priority' => $ruleData['priority'],
            ]);
        }

        return response()->json(['message' => 'Rules reordered.'], 200);
    }
}
