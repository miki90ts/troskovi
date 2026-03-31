<?php

namespace App\Http\Controllers;

use App\Services\BankAccountService;
use App\Services\ReportService;
use App\Services\SpendingTargetService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private TransactionService $transactionService,
        private BankAccountService $bankAccountService,
        private SpendingTargetService $spendingTargetService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'summary' => $this->reportService->getSummary($user, 'monthly'),
            'budgetProgress' => $this->spendingTargetService->getProgress($user, 'monthly'),
            'recentTransactions' => $this->transactionService->getRecent($user, 5)
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'type' => $t->type->value,
                    'amount' => (float) $t->amount,
                    'date' => $t->date->toDateString(),
                    'description' => $t->description,
                    'category' => $t->category ? ['name' => $t->category->name, 'icon' => $t->category->icon, 'color' => $t->category->color] : null,
                    'bank_account' => $t->bankAccount ? ['name' => $t->bankAccount->name] : null,
                    'payment_method' => $t->payment_method->value,
                ]),
            'accounts' => $this->bankAccountService->list($user)->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'bank_name' => $a->bank_name,
                'currency' => $a->currency,
                'color' => $a->color,
                'icon' => $a->icon,
                'current_balance' => (float) $a->current_balance,
            ]),
        ]);
    }
}
