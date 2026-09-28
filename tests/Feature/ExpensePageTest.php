<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('expense page preserves multiple category filters and narrows results', function () {
    $user = User::factory()->create();

    $fuelCategory = $user->categories()->create([
        'name' => 'Gorivo',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => '#ef4444',
        'is_system' => false,
    ]);

    $groceriesCategory = $user->categories()->create([
        'name' => 'Namirnice',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => '#10b981',
        'is_system' => false,
    ]);

    $rentCategory = $user->categories()->create([
        'name' => 'Kirija',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => '#6366f1',
        'is_system' => false,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 5000,
        'date' => '2026-05-01',
        'description' => 'Sipanje goriva',
        'category_id' => $fuelCategory->id,
        'payment_method' => PaymentMethod::Cash,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 3200,
        'date' => '2026-05-02',
        'description' => 'Kupovina namirnica',
        'category_id' => $groceriesCategory->id,
        'payment_method' => PaymentMethod::BankAccount,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 25000,
        'date' => '2026-05-03',
        'description' => 'Kirija za stan',
        'category_id' => $rentCategory->id,
        'payment_method' => PaymentMethod::BankAccount,
    ]);

    $categoryIds = implode(',', [$fuelCategory->id, $groceriesCategory->id]);

    $this->actingAs($user)
        ->get(route('expenses.index', ['category_ids' => $categoryIds]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('expenses/Index')
                ->where('filters.category_ids', $categoryIds)
                ->where('transactions.meta.total', 2),
        );
});

test('expense page applies selected per page value and preserves it in inertia props', function () {
    $user = User::factory()->create();

    $category = $user->categories()->create([
        'name' => 'Režije',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => '#0f766e',
        'is_system' => false,
    ]);

    foreach (range(1, 35) as $index) {
        $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 1000 + $index,
            'date' => sprintf('2026-05-%02d', ($index % 28) + 1),
            'description' => "Trošak {$index}",
            'category_id' => $category->id,
            'payment_method' => PaymentMethod::Cash,
        ]);
    }

    $this->actingAs($user)
        ->get(route('expenses.index', ['per_page' => '30']))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('expenses/Index')
                ->where('filters.per_page', '30')
                ->where('transactions.meta.per_page', 30)
                ->where('transactions.meta.total', 35)
                ->has('transactions.data', 30),
        );
});

test('expense page falls back to default per page when an unsupported value is requested', function () {
    $user = User::factory()->create();

    $category = $user->categories()->create([
        'name' => 'Kupovina',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => '#9333ea',
        'is_system' => false,
    ]);

    foreach (range(1, 20) as $index) {
        $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 500 + $index,
            'date' => sprintf('2026-04-%02d', ($index % 28) + 1),
            'description' => "Kupovina {$index}",
            'category_id' => $category->id,
            'payment_method' => PaymentMethod::BankAccount,
        ]);
    }

    $this->actingAs($user)
        ->get(route('expenses.index', ['per_page' => '17']))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('expenses/Index')
                ->where('filters.per_page', '15')
                ->where('transactions.meta.per_page', 15)
                ->where('transactions.meta.total', 20)
                ->has('transactions.data', 15),
        );
});

test('expense page returns display amounts in the user default currency', function () {
    $rsd = Currency::query()->firstOrCreate(
        ['iso_code' => 'RSD'],
        ['name' => 'Serbian Dinar', 'symbol' => 'RSD', 'active' => true],
    );

    $eur = Currency::query()->firstOrCreate(
        ['iso_code' => 'EUR'],
        ['name' => 'Euro', 'symbol' => 'EUR', 'active' => true],
    );

    $eur->exchangeRates()->updateOrCreate(
        ['date' => '2026-05-10'],
        ['rate' => 117],
    );

    $user = User::factory()->create([
        'default_currency_id' => $eur->id,
    ]);

    $category = $user->categories()->create([
        'name' => 'Putovanje',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => '#ef4444',
        'is_system' => false,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 234,
        'base_amount' => 234,
        'exchange_rate' => 1,
        'currency_id' => $rsd->id,
        'date' => '2026-05-10',
        'description' => 'Avans za hotel',
        'category_id' => $category->id,
        'payment_method' => PaymentMethod::Cash,
    ]);

    $this->actingAs($user)
        ->get(route('expenses.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('expenses/Index')
                ->where('defaultCurrency.iso_code', 'EUR')
                ->where('latestExchangeRates.EUR', 117)
                ->where('transactions.data.0.base_amount', 234)
                ->where('transactions.data.0.currency.iso_code', 'RSD'),
        );
});
