<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDebtRequest;
use App\Http\Requests\UpdateDebtRequest;
use App\Http\Resources\DebtResource;
use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class DebtController extends Controller
{
    public function __construct(private DebtService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $debts = $this->service->list($request->user(), $request->all());

        return DebtResource::collection($debts);
    }

    public function store(StoreDebtRequest $request): JsonResponse
    {
        $debt = $this->service->create($request->user(), $request->validated());

        return (new DebtResource($debt))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Debt $debt): DebtResource
    {
        Gate::authorize('view', $debt);

        return new DebtResource($debt->loadCount('transactions'));
    }

    public function update(UpdateDebtRequest $request, Debt $debt): DebtResource
    {
        Gate::authorize('update', $debt);

        $debt = $this->service->update($debt, $request->validated());

        return new DebtResource($debt);
    }

    public function destroy(Debt $debt): JsonResponse
    {
        Gate::authorize('delete', $debt);

        $this->service->delete($debt);

        return response()->json(['message' => 'Debt deleted']);
    }

    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->service->getSummary($request->user()));
    }
}
