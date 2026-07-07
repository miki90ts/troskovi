<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountTransferResource;
use App\Http\Resources\BankAccountOverviewResource;
use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Models\Currency;
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
        $accounts = $this->service->list($request->user(), includeArchived: true);
        $transfers = $this->service->listTransfers($request->user());

        return Inertia::render('bank-accounts/Index', [
            'accounts' => BankAccountResource::collection($accounts),
            'transfers' => AccountTransferResource::collection($transfers),
            'currencies' => Currency::query()
                ->where('active', true)
                ->orderBy('iso_code')
                ->get(['id', 'iso_code', 'name', 'symbol']),
            'latestExchangeRates' => Currency::query()
                ->where('active', true)
                ->orderBy('iso_code')
                ->get()
                ->mapWithKeys(function (Currency $currency) {
                    if ($currency->iso_code === 'RSD') {
                        return [$currency->iso_code => 1.0];
                    }

                    return [
                        $currency->iso_code => (float) ($currency->exchangeRates()
                            ->where('date', '<=', now()->toDateString())
                            ->orderByDesc('date')
                            ->value('rate') ?? 0),
                    ];
                }),
            'defaultCurrencyId' => $request->user()->default_currency_id,
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
