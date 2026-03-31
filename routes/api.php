<?php

use App\Http\Controllers\Api\V1\BankAccountController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\LoyaltyCardController;
use App\Http\Controllers\Api\V1\PdfExportController;
use App\Http\Controllers\Api\V1\RecurringTransactionController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SpendingTargetController;
use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Bank Accounts
    Route::apiResource('bank-accounts', BankAccountController::class);
    Route::post('bank-accounts/{bankAccount}/restore', [BankAccountController::class, 'restore']);
    Route::get('bank-accounts/{bankAccount}/overview', [BankAccountController::class, 'overview']);
    Route::post('bank-accounts/transfer', [BankAccountController::class, 'transfer']);

    // Transactions
    Route::apiResource('transactions', TransactionController::class);
    Route::get('transactions/{transaction}/receipt', [TransactionController::class, 'receipt']);

    // Categories
    Route::apiResource('categories', CategoryController::class)->except(['show']);

    // Recurring Transactions
    Route::apiResource('recurring-transactions', RecurringTransactionController::class);

    // Spending Targets
    Route::apiResource('spending-targets', SpendingTargetController::class)->except(['show']);

    // Loyalty Cards
    Route::apiResource('loyalty-cards', LoyaltyCardController::class)->except(['show']);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('summary', [ReportController::class, 'summary']);
        Route::get('income-vs-expenses', [ReportController::class, 'incomeVsExpenses']);
        Route::get('net-balance', [ReportController::class, 'netBalance']);
        Route::get('expense-breakdown', [ReportController::class, 'expenseBreakdown']);
        Route::get('income-breakdown', [ReportController::class, 'incomeBreakdown']);
        Route::get('cash-vs-bank', [ReportController::class, 'cashVsBank']);
        Route::get('spending-progress', [ReportController::class, 'spendingProgress']);
    });

    // PDF Export
    Route::prefix('export')->group(function () {
        Route::get('transactions/pdf', [PdfExportController::class, 'transactions']);
        Route::get('report/pdf', [PdfExportController::class, 'report']);
    });
});
