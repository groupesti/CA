<?php

declare(strict_types=1);

namespace CA\Policy\Http\Controllers;

use CA\Policy\Http\Requests\CreateNameConstraintRequest;
use CA\Policy\Http\Resources\NameConstraintResource;
use CA\Policy\Models\NameConstraint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class NameConstraintController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = NameConstraint::query();

        if ($request->has('ca_id')) {
            $query->where('ca_id', $request->input('ca_id'));
        }

        if ($request->has('policy_id')) {
            $query->where('policy_id', $request->input('policy_id'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        $constraints = $query->paginate($request->integer('per_page', 15));

        return NameConstraintResource::collection($constraints);
    }

    public function store(CreateNameConstraintRequest $request): NameConstraintResource
    {
        $constraint = NameConstraint::create($request->validated());

        return new NameConstraintResource($constraint);
    }

    public function show(int $id): NameConstraintResource
    {
        $constraint = NameConstraint::findOrFail($id);

        return new NameConstraintResource($constraint);
    }

    public function update(CreateNameConstraintRequest $request, int $id): NameConstraintResource
    {
        $constraint = NameConstraint::findOrFail($id);
        $constraint->update($request->validated());

        return new NameConstraintResource($constraint);
    }

    public function destroy(int $id): JsonResponse
    {
        $constraint = NameConstraint::findOrFail($id);
        $constraint->delete();

        return response()->json(['message' => 'Name constraint deleted.'], 200);
    }
}
