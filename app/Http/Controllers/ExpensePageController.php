<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpensePageController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = array_merge($request->all(), ['type' => 'expense']);
        $transactions = $this->transactionService->list($user, $filters);
        $categories = $this->categoryService->list($user, 'expense');

        $accounts = $user->bankAccounts()->active()->orderBy('name')->get()->map(fn($a) => [
            'id' => $a->id,
            'name' => $a->name,
        ]);

        return Inertia::render('expenses/Index', [
            'transactions' => \App\Http\Resources\TransactionResource::collection($transactions),
            'categories' => CategoryResource::collection($categories),
            'accounts' => $accounts,
            'filters' => $request->only(['date_from', 'date_to', 'category_id', 'payment_method', 'bank_account_id', 'search']),
        ]);
    }
}
