<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard summary uses only the current month totals', function () {
    CarbonImmutable::setTestNow('2026-04-20 12:00:00');

    $user = User::factory()->create();

    $user->transactions()->create([
        'type' => TransactionType::Income,
        'amount' => 25000,
        'date' => '2026-04-10',
        'description' => 'Aprilska plata',
        'payment_method' => PaymentMethod::Cash,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Expense,
        'amount' => 7000,
        'date' => '2026-04-12',
        'description' => 'Aprilski trošak',
        'payment_method' => PaymentMethod::Cash,
    ]);

    $user->transactions()->create([
        'type' => TransactionType::Income,
        'amount' => 57035,
        'date' => '2026-03-15',
        'description' => 'Martovski prihod',
        'payment_method' => PaymentMethod::Cash,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('summary.total_income', 25000)
                ->where('summary.total_expenses', 7000)
                ->where('summary.period_start', '2026-04-01')
                ->where('summary.period_end', '2026-04-30'),
        );

    CarbonImmutable::setTestNow();
});
