<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Resources\RecurringTransactionResource;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class RecurringTransactionController extends Controller
{
    public function __construct(private RecurringTransactionService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $recurring = $this->service->list($request->user());

        return RecurringTransactionResource::collection($recurring);
    }

    public function store(StoreRecurringTransactionRequest $request): JsonResponse
    {
        $recurring = $this->service->create($request->user(), $request->validated());

        return (new RecurringTransactionResource($recurring->load(['category', 'bankAccount'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RecurringTransaction $recurringTransaction): RecurringTransactionResource
    {
        Gate::authorize('view', $recurringTransaction);

        return new RecurringTransactionResource(
            $recurringTransaction->load(['category', 'bankAccount'])
        );
    }

    public function update(StoreRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RecurringTransactionResource
    {
        Gate::authorize('update', $recurringTransaction);

        $updated = $this->service->update($recurringTransaction, $request->validated());

        return new RecurringTransactionResource($updated);
    }

    public function destroy(RecurringTransaction $recurringTransaction): JsonResponse
    {
        Gate::authorize('delete', $recurringTransaction);

        $this->service->delete($recurringTransaction);

        return response()->json(['message' => 'Recurring transaction deactivated']);
    }
}
