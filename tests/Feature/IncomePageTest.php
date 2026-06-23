<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('income page preserves payment method filter in inertia props', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('incomes.index', ['payment_method' => 'cash']))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('incomes/Index')
                ->where('filters.payment_method', 'cash'),
        );
});

test('income page preserves multiple category filters and narrows results', function () {
    $user = User::factory()->create();

    $salaryCategory = $user->categories()->create([
        'name' => 'Plata',
        'type' => TransactionType::Income,
        'icon' => null,
        'color' => '#22c55e',
        'is_system' => false,
    ]);

    $fuelRefundCategory = $user->categories()->create([
        'name' => 'Gorivo refundacija',
        'type' => TransactionType::Income,
        'icon' => null,
        'color' => '#3b82f6',
        'is_system' => false,
    ]);

    $bonusCategory = $user->categories()->create([
        'name' => 'Bonus',
        'type' => TransactionType::Income,
        'icon' => null,
        'color' => '#f59e0b',
        'is_system' => false,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Income,
        'amount' => 100000,
        'date' => '2026-05-01',
        'description' => 'Majska plata',
        'category_id' => $salaryCategory->id,
        'payment_method' => PaymentMethod::Cash,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Income,
        'amount' => 3500,
        'date' => '2026-05-02',
        'description' => 'Refundacija goriva',
        'category_id' => $fuelRefundCategory->id,
        'payment_method' => PaymentMethod::BankAccount,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Income,
        'amount' => 7000,
        'date' => '2026-05-03',
        'description' => 'Kvartalni bonus',
        'category_id' => $bonusCategory->id,
        'payment_method' => PaymentMethod::Cash,
    ]);

    $categoryIds = implode(',', [$salaryCategory->id, $fuelRefundCategory->id]);

    $this->actingAs($user)
        ->get(route('incomes.index', ['category_ids' => $categoryIds]))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('incomes/Index')
                ->where('filters.category_ids', $categoryIds)
                ->where('transactions.meta.total', 2),
        );
});

test('income page applies selected per page value and preserves it in inertia props', function () {
    $user = User::factory()->create();

    $category = $user->categories()->create([
        'name' => 'Honorari',
        'type' => TransactionType::Income,
        'icon' => null,
        'color' => '#2563eb',
        'is_system' => false,
    ]);

    foreach (range(1, 35) as $index) {
        $user->transactions()->create([
            'type' => TransactionType::Income,
            'amount' => 10000 + $index,
            'date' => sprintf('2026-06-%02d', ($index % 28) + 1),
            'description' => "Prihod {$index}",
            'category_id' => $category->id,
            'payment_method' => PaymentMethod::BankAccount,
        ]);
    }

    $this->actingAs($user)
        ->get(route('incomes.index', ['per_page' => '30']))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('incomes/Index')
                ->where('filters.per_page', '30')
                ->where('transactions.meta.per_page', 30)
                ->where('transactions.meta.total', 35)
                ->has('transactions.data', 30),
        );
});
