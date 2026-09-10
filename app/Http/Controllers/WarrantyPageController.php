<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\TransactionResource;
use App\Services\CategoryService;
use App\Services\MoneyService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarrantyPageController extends Controller
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
        $filters = array_merge($request->all(), [
            'type' => 'expense',
            'is_warranty' => true,
        ]);

        $transactions = $this->transactionService->list($user, $filters);
        $defaultCurrency = $this->moneyService->resolveUserCurrency($user);

        $counts = [
            'active' => $user->transactions()
                ->warranty()
                ->where('warranty_expires_at', '>=', now())
                ->count(),
            'expiring_soon' => $user->transactions()
                ->warranty()
                ->where('warranty_expires_at', '>=', now())
                ->where('warranty_expires_at', '<=', now()->addDays(30))
                ->count(),
            'expired' => $user->transactions()
                ->warranty()
                ->where('warranty_expires_at', '<', now())
                ->count(),
        ];

        return Inertia::render('warranties/Index', [
            'transactions' => TransactionResource::collection($transactions),
            'counts' => $counts,
            'categories' => CategoryResource::collection(
                $this->categoryService->list($user, 'expense')
            ),
            'filters' => array_merge(
                $request->only([
                    'search',
                    'status',
                    'category_id',
                    'category_ids',
                    'payment_method',
                    'date_from',
                    'date_to',
                ]),
                ['per_page' => (string) $transactions->perPage()],
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
