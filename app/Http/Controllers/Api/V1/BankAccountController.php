<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Http\Resources\AccountTransferResource;
use App\Http\Resources\BankAccountOverviewResource;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class BankAccountController extends Controller
{
    public function __construct(private BankAccountService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $includeArchived = $request->boolean('include_archived');
        $accounts = $this->service->list($request->user(), $includeArchived);

        return BankAccountResource::collection($accounts);
    }

    public function store(StoreBankAccountRequest $request): JsonResponse
    {
        $account = $this->service->create($request->user(), $request->validated());

        return (new BankAccountResource($account))
            ->response()
            ->setStatusCode(201);
    }

    public function show(BankAccount $bankAccount): BankAccountResource
    {
        Gate::authorize('view', $bankAccount);

        return new BankAccountResource($bankAccount);
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount): BankAccountResource
    {
        Gate::authorize('update', $bankAccount);

        $account = $this->service->update($bankAccount, $request->validated());

        return new BankAccountResource($account);
    }

    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        Gate::authorize('delete', $bankAccount);

        $this->service->archive($bankAccount);

        return response()->json(['message' => 'Account archived']);
    }

    public function restore(BankAccount $bankAccount): BankAccountResource
    {
        Gate::authorize('update', $bankAccount);

        $account = $this->service->restore($bankAccount);

        return new BankAccountResource($account);
    }

    public function overview(BankAccount $bankAccount): BankAccountOverviewResource
    {
        Gate::authorize('view', $bankAccount);

        return new BankAccountOverviewResource($bankAccount);
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $transfer = $this->service->transfer($request->user(), $request->validated());

        return (new AccountTransferResource($transfer->load(['fromAccount', 'toAccount'])))
            ->response()
            ->setStatusCode(201);
    }
}
