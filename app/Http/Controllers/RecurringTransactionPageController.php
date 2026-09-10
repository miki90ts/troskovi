<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\RecurringTransactionResource;
use App\Models\Debt;
use App\Services\CategoryService;
use App\Services\MoneyService;
use App\Services\RecurringTransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecurringTransactionPageController extends Controller
{
    public function __construct(
        private RecurringTransactionService $recurringTransactionService,
        private CategoryService $categoryService,
        private MoneyService $moneyService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $user->loadMissing('defaultCurrency');
        $type = in_array($request->string('type')->toString(), ['expense', 'income'], true)
            ? $request->string('type')->toString()
            : 'expense';
        $filters = array_merge($request->all(), ['type' => $type]);
        $recurringTransactions = $this->recurringTransactionService->paginate($user, $filters);
        $categories = $this->categoryService->list($user);
        $defaultCurrency = $this->moneyService->resolveUserCurrency($user);
        $accounts = $user->bankAccounts()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn ($account) => [
                'id' => $account->id,
                'name' => $account->name,
                'currency' => $account->currency,
                'currency_id' => $account->currency_id,
            ]);
        $debts = Debt::where('user_id', $user->id)
            ->whereIn('status', ['active', 'overdue'])
            ->orderBy('person_name')
            ->get(['id', 'type', 'person_name', 'remaining_amount', 'status']);

        return Inertia::render('recurring-transactions/Index', [
            'recurringTransactions' => RecurringTransactionResource::collection($recurringTransactions),
            'counts' => [
                'expense' => $user->recurringTransactions()->where('type', 'expense')->count(),
                'income' => $user->recurringTransactions()->where('type', 'income')->count(),
            ],
            'categories' => CategoryResource::collection($categories),
            'accounts' => $accounts,
            'debts' => $debts,
            'filters' => array_merge(
                $request->only([
                    'search',
                    'category_id',
                    'category_ids',
                    'payment_method',
                    'frequency',
                    'status',
                    'date_from',
                    'date_to',
                ]),
                [
                    'type' => $type,
                    'per_page' => (string) $recurringTransactions->perPage(),
                ],
            ),
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
