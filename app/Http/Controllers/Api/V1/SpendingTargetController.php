<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSpendingTargetRequest;
use App\Http\Resources\SpendingTargetResource;
use App\Models\SpendingTarget;
use App\Services\SpendingTargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SpendingTargetController extends Controller
{
    public function __construct(private SpendingTargetService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return SpendingTargetResource::collection($this->service->list($request->user()));
    }

    public function store(StoreSpendingTargetRequest $request): JsonResponse
    {
        $spendingTarget = $this->service->create($request->user(), $request->validated());

        return (new SpendingTargetResource($spendingTarget))
            ->response()
            ->setStatusCode(201);
    }

    public function update(StoreSpendingTargetRequest $request, SpendingTarget $spendingTarget): SpendingTargetResource
    {
        Gate::authorize('update', $spendingTarget);

        return new SpendingTargetResource($this->service->update($spendingTarget, $request->validated()));
    }

    public function destroy(SpendingTarget $spendingTarget): JsonResponse
    {
        Gate::authorize('delete', $spendingTarget);

        $this->service->delete($spendingTarget);

        return response()->json(['message' => 'Spending target deleted']);
    }
}
