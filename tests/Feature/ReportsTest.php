<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Carbon\CarbonImmutable;
use Laravel\Sanctum\Sanctum;

test('reports summary uses only the selected current monthly period', function () {
    CarbonImmutable::setTestNow('2026-04-20 12:00:00');

    $user = User::factory()->create();
    $rentCategory = Category::create([
        'user_id' => $user->id,
        'name' => 'Kirija',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => null,
        'is_system' => false,
    ]);
    $foodCategory = Category::create([
        'user_id' => $user->id,
        'name' => 'Hrana',
        'type' => TransactionType::Expense,
        'icon' => null,
        'color' => null,
        'is_system' => false,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Income,
        'amount' => 25000,
        'date' => '2026-04-10',
        'description' => 'Aprilska plata',
        'payment_method' => PaymentMethod::Cash,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 12000,
        'date' => '2026-04-08',
        'description' => 'Kirija april',
        'payment_method' => PaymentMethod::Cash,
        'category_id' => $rentCategory->id,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 3000,
        'date' => '2026-04-14',
        'description' => 'Kupovina',
        'payment_method' => PaymentMethod::Cash,
        'category_id' => $foodCategory->id,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Income,
        'amount' => 57035,
        'date' => '2026-03-15',
        'description' => 'Martovska plata',
        'payment_method' => PaymentMethod::Cash,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 10000,
        'date' => '2026-03-09',
        'description' => 'Kirija mart',
        'payment_method' => PaymentMethod::Cash,
        'category_id' => $foodCategory->id,
    ]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/reports/summary?period=monthly')
        ->assertOk()
        ->assertJsonPath('data.total_income', 25000)
        ->assertJsonPath('data.total_expenses', 15000)
        ->assertJsonPath('data.income_change', -56.2)
        ->assertJsonPath('data.biggest_expense_category', 'Kirija')
        ->assertJsonPath('data.biggest_expense_amount', 12000)
        ->assertJsonPath('data.mom_change', -78.7)
        ->assertJsonPath('data.period_start', '2026-04-01')
        ->assertJsonPath('data.period_end', '2026-04-30');

    CarbonImmutable::setTestNow();
});
