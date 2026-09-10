<?php

use App\Enums\PaymentMethod;
use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('warranty page filters and paginates records while exposing currency data', function () {
    $user = User::factory()->create();
    $currency = Currency::query()->where('iso_code', 'RSD')->firstOrFail();
    $category = $user->categories()->create([
        'name' => 'Tehnika',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => '#2563eb',
        'is_system' => false,
    ]);

    foreach (range(1, 35) as $index) {
        $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 1000 + $index,
            'currency_id' => $currency->id,
            'exchange_rate' => 1,
            'base_amount' => 1000 + $index,
            'date' => now()->subDay(),
            'description' => "Garancija {$index}",
            'category_id' => $category->id,
            'payment_method' => PaymentMethod::Cash,
            'is_warranty' => true,
            'warranty_expires_at' => now()->addYear(),
        ]);
    }

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 500,
        'currency_id' => $currency->id,
        'exchange_rate' => 1,
        'base_amount' => 500,
        'date' => now()->subYears(3),
        'description' => 'Stara garancija',
        'payment_method' => PaymentMethod::Cash,
        'is_warranty' => true,
        'warranty_expires_at' => now()->subYear(),
    ]);

    $this->actingAs($user)
        ->get(route('warranties.index', [
            'search' => 'Garancija',
            'status' => 'active',
            'category_ids' => (string) $category->id,
            'payment_method' => 'cash',
            'per_page' => '30',
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('warranties/Index')
                ->where('filters.per_page', '30')
                ->where('filters.status', 'active')
                ->where('transactions.meta.per_page', 30)
                ->where('transactions.meta.total', 35)
                ->has('transactions.data', 30)
                ->where('transactions.data.0.currency.iso_code', 'RSD')
                ->where('defaultCurrency.iso_code', 'RSD')
                ->where('counts.active', 35)
                ->where('counts.expired', 1),
        );
});

test('recurring page applies tab filters and supported page size', function () {
    $user = User::factory()->create();
    $currency = Currency::query()->where('iso_code', 'RSD')->firstOrFail();

    foreach (range(1, 35) as $index) {
        $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 500 + $index,
            'currency_id' => $currency->id,
            'description' => "Pretplata {$index}",
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->addMonth(),
            'payment_method' => PaymentMethod::Cash,
            'is_active' => true,
        ]);
    }

    $user->recurringTransactions()->create([
        'type' => TransactionType::Income,
        'amount' => 100000,
        'currency_id' => $currency->id,
        'description' => 'Plata',
        'frequency' => RecurringFrequency::Monthly,
        'next_due_date' => now()->addMonth(),
        'payment_method' => PaymentMethod::BankAccount,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get('/recurring-transactions?'.http_build_query([
            'type' => 'expense',
            'search' => 'Pretplata',
            'frequency' => 'monthly',
            'status' => 'active',
            'per_page' => '30',
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('recurring-transactions/Index')
                ->where('filters.type', 'expense')
                ->where('filters.per_page', '30')
                ->where('filters.frequency', 'monthly')
                ->where('recurringTransactions.meta.per_page', 30)
                ->where('recurringTransactions.meta.total', 35)
                ->has('recurringTransactions.data', 30)
                ->where('counts.expense', 35)
                ->where('counts.income', 1)
                ->where('defaultCurrency.iso_code', 'RSD'),
        );
});
