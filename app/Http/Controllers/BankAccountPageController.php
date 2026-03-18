<?php

namespace App\Http\Controllers;

use App\Http\Resources\BankAccountOverviewResource;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountPageController extends Controller
{
    public function __construct(private BankAccountService $service) {}

    public function index(Request $request): Response
    {
        $accounts = $this->service->list($request->user(), $request->boolean('include_archived'));

        return Inertia::render('bank-accounts/Index', [
            'accounts' => BankAccountResource::collection($accounts),
        ]);
    }

    public function show(Request $request, BankAccount $bankAccount): Response
    {
        Gate::authorize('view', $bankAccount);

        return Inertia::render('bank-accounts/Show', [
            'account' => new BankAccountOverviewResource($bankAccount),
        ]);
    }
}
