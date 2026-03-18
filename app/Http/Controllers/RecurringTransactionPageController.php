<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\RecurringTransactionResource;
use App\Services\CategoryService;
use App\Services\RecurringTransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecurringTransactionPageController extends Controller
{
    public function __construct(
        private RecurringTransactionService $recurringTransactionService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $recurringTransactions = $this->recurringTransactionService->list($user);
        $categories = $this->categoryService->list($user);
        $accounts = $user->bankAccounts()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn($account) => [
                'id' => $account->id,
                'name' => $account->name,
            ]);

        return Inertia::render('recurring-transactions/Index', [
            'recurringTransactions' => RecurringTransactionResource::collection($recurringTransactions),
            'categories' => CategoryResource::collection($categories),
            'accounts' => $accounts,
        ]);
    }
}
