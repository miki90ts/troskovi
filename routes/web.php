<?php

use App\Http\Controllers\BankAccountPageController;
use App\Http\Controllers\CategoryPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpensePageController;
use App\Http\Controllers\IncomePageController;
use App\Http\Controllers\RecurringTransactionPageController;
use App\Http\Controllers\ReportPageController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('bank-accounts', [BankAccountPageController::class, 'index'])->name('bank-accounts.index');
    Route::get('bank-accounts/{bank_account}', [BankAccountPageController::class, 'show'])->name('bank-accounts.show');

    Route::get('expenses', [ExpensePageController::class, 'index'])->name('expenses.index');
    Route::get('incomes', [IncomePageController::class, 'index'])->name('incomes.index');
    Route::get('recurring-transactions', [RecurringTransactionPageController::class, 'index'])->name('recurring-transactions.index');

    Route::get('reports', [ReportPageController::class, 'index'])->name('reports.index');
    Route::get('categories', [CategoryPageController::class, 'index'])->name('categories.index');
});

require __DIR__ . '/settings.php';
