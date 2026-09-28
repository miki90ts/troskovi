<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('transactions pdf export accepts multiple category filters', function () {
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

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 4200,
        'date' => '2026-05-01',
        'description' => 'Sipanje goriva',
        'category_id' => $fuelCategory->id,
        'payment_method' => PaymentMethod::Cash,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 2300,
        'date' => '2026-05-02',
        'description' => 'Kupovina namirnica',
        'category_id' => $groceriesCategory->id,
        'payment_method' => PaymentMethod::BankAccount,
    ]);

    Sanctum::actingAs($user);

    $response = $this->get(
        '/api/v1/export/transactions/pdf?'.http_build_query([
            'type' => 'expense',
            'category_ids' => implode(',', [$fuelCategory->id, $groceriesCategory->id]),
        ]),
    );

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
