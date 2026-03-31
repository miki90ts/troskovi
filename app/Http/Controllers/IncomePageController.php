<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\TransactionResource;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncomePageController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = array_merge($request->all(), ['type' => 'income']);
        $transactions = $this->transactionService->list($user, $filters);
        $categories = $this->categoryService->list($user, 'income');

        $accounts = $user->bankAccounts()->active()->orderBy('name')->get()->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
        ]);

        return Inertia::render('incomes/Index', [
            'transactions' => TransactionResource::collection($transactions),
            'categories' => CategoryResource::collection($categories),
            'accounts' => $accounts,
            'filters' => $request->only(['date_from', 'date_to', 'category_id', 'bank_account_id', 'search']),
        ]);
    }
}
