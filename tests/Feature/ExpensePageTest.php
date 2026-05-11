<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
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
            fn(Assert $page) => $page
                ->component('expenses/Index')
                ->where('filters.category_ids', $categoryIds)
                ->where('transactions.meta.total', 2),
        );
});
