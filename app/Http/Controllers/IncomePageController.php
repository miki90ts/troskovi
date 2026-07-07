<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\TransactionResource;
use App\Models\Debt;
use App\Services\CategoryService;
use App\Services\MoneyService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncomePageController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private CategoryService $categoryService,
        private MoneyService $moneyService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('defaultCurrency');
        $filters = array_merge($request->all(), ['type' => 'income']);
        $transactions = $this->transactionService->list($user, $filters);
        $categories = $this->categoryService->list($user, 'income');
        $defaultCurrency = $this->moneyService->resolveUserCurrency($user);

        $accounts = $user->bankAccounts()->active()->orderBy('name')->get()->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->name,
        ]);

        $debts = Debt::where('user_id', $user->id)
            ->whereIn('status', ['active', 'overdue'])
            ->orderBy('person_name')
            ->get(['id', 'type', 'person_name', 'remaining_amount', 'status']);

        $filters = array_merge(
            $request->only([
                'date_from',
                'date_to',
                'category_id',
                'category_ids',
                'payment_method',
                'bank_account_id',
                'search',
            ]),
            ['per_page' => (string) $transactions->perPage()],
        );

        return Inertia::render('incomes/Index', [
            'transactions' => TransactionResource::collection($transactions),
            'categories' => CategoryResource::collection($categories),
            'accounts' => $accounts,
            'debts' => $debts,
            'filters' => $filters,
            'defaultCurrency' => [
                'id' => $defaultCurrency->id,
                'iso_code' => $defaultCurrency->iso_code,
                'name' => $defaultCurrency->name,
                'symbol' => $defaultCurrency->symbol,
            ],
            'latestExchangeRates' => $this->moneyService->activeCurrencies()
                ->mapWithKeys(function ($currency) {
                    if ($currency->iso_code === MoneyService::BASE_CURRENCY_ISO) {
                        return [$currency->iso_code => 1.0];
                    }

                    return [
                        $currency->iso_code => (float) ($currency->exchangeRates()
                            ->where('date', '<=', now()->toDateString())
                            ->orderByDesc('date')
                            ->value('rate') ?? 0),
                    ];
                }),
        ]);
    }
}
