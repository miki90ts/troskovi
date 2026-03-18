<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = $this->service->list($request->user(), $request->all());

        return TransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->service->create($request->user(), $request->validated());

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Transaction $transaction): TransactionResource
    {
        Gate::authorize('view', $transaction);

        return new TransactionResource($transaction->load(['category', 'bankAccount']));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): TransactionResource
    {
        Gate::authorize('update', $transaction);

        $transaction = $this->service->update($transaction, $request->validated());

        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        Gate::authorize('delete', $transaction);

        $this->service->delete($transaction);

        return response()->json(['message' => 'Transaction deleted']);
    }

    public function receipt(Transaction $transaction): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        Gate::authorize('view', $transaction);

        if (! $transaction->receipt_path || ! Storage::disk('local')->exists($transaction->receipt_path)) {
            return response()->json(['message' => 'No receipt found'], 404);
        }

        return response()->download(Storage::disk('local')->path($transaction->receipt_path));
    }
}
