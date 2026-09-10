<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MultiCurrencyFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_debt_creation_accepts_explicit_currency_id(): void
    {
        $user = User::factory()->create();
        $eur = Currency::query()->where('iso_code', 'EUR')->firstOrFail();

        $eur->exchangeRates()->create([
            'date' => '2026-07-01',
            'rate' => 117.5,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/debts', [
            'type' => 'i_owe',
            'person_name' => 'Banka',
            'description' => 'Kredit u EUR',
            'amount' => 100,
            'currency_id' => $eur->id,
            'date' => '2026-07-03',
        ])
            ->assertCreated()
            ->assertJsonPath('data.currency.iso_code', 'EUR')
            ->assertJsonPath('data.exchange_rate', 117.5)
            ->assertJsonPath('data.base_amount', 11750);
    }

    public function test_cash_recurring_creation_accepts_explicit_currency_id(): void
    {
        $user = User::factory()->create();
        $eur = Currency::query()->where('iso_code', 'EUR')->firstOrFail();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 25,
            'currency_id' => $eur->id,
            'description' => 'Pretplata',
            'frequency' => 'monthly',
            'next_due_date' => '2026-07-10',
            'payment_method' => 'cash',
        ])
            ->assertCreated()
            ->assertJsonPath('data.currency.iso_code', 'EUR');

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'description' => 'Pretplata',
            'currency_id' => $eur->id,
        ]);
    }

    public function test_recurring_currency_follows_bank_account_or_user_default_without_explicit_input(): void
    {
        $eur = Currency::query()->where('iso_code', 'EUR')->firstOrFail();
        $usd = Currency::query()->where('iso_code', 'USD')->firstOrFail();
        $user = User::factory()->create(['default_currency_id' => $eur->id]);
        $account = $user->bankAccounts()->create([
            'name' => 'USD račun',
            'bank_name' => 'Test banka',
            'account_number' => '1111222233334444',
            'currency' => 'USD',
            'currency_id' => $usd->id,
            'initial_balance' => 0,
            'is_archived' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 25,
            'description' => 'Pretplata sa računa',
            'frequency' => 'monthly',
            'next_due_date' => '2026-07-10',
            'payment_method' => 'bank_account',
            'bank_account_id' => $account->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.currency.iso_code', 'USD');

        $recurringId = $response->json('data.id');

        $this->putJson("/api/v1/recurring-transactions/{$recurringId}", [
            'payment_method' => 'cash',
            'bank_account_id' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.currency.iso_code', 'EUR');
    }

    public function test_spending_target_creation_accepts_explicit_currency_id(): void
    {
        $user = User::factory()->create();
        $usd = Currency::query()->where('iso_code', 'USD')->firstOrFail();
        $category = $user->categories()->create([
            'name' => 'Software',
            'type' => TransactionType::Expense,
            'icon' => null,
            'color' => null,
            'is_system' => false,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/spending-targets', [
            'period' => 'monthly',
            'target_amount' => 300,
            'currency_id' => $usd->id,
            'category_id' => $category->id,
            'is_active' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.currency.iso_code', 'USD');
    }

    public function test_cross_currency_transfer_uses_destination_amount_for_target_balance(): void
    {
        $user = User::factory()->create();
        $eur = Currency::query()->where('iso_code', 'EUR')->firstOrFail();
        $rsd = Currency::query()->where('iso_code', 'RSD')->firstOrFail();

        $eur->exchangeRates()->create([
            'date' => '2026-07-01',
            'rate' => 117.5,
        ]);

        $fromAccount = $user->bankAccounts()->create([
            'name' => 'EUR račun',
            'bank_name' => 'Test banka',
            'account_number' => '1111222233334444',
            'currency' => 'EUR',
            'currency_id' => $eur->id,
            'color' => null,
            'icon' => null,
            'initial_balance' => 500,
            'is_archived' => false,
        ]);
        $toAccount = $user->bankAccounts()->create([
            'name' => 'RSD račun',
            'bank_name' => 'Test banka',
            'account_number' => '5555666677778888',
            'currency' => 'RSD',
            'currency_id' => $rsd->id,
            'color' => null,
            'icon' => null,
            'initial_balance' => 10000,
            'is_archived' => false,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bank-accounts/transfer', [
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 100,
            'description' => 'EUR u RSD',
            'date' => '2026-07-03',
        ])
            ->assertCreated()
            ->assertJsonPath('data.from_currency.iso_code', 'EUR')
            ->assertJsonPath('data.to_currency.iso_code', 'RSD')
            ->assertJsonPath('data.exchange_rate', 117.5)
            ->assertJsonPath('data.base_amount', 11750)
            ->assertJsonPath('data.to_amount', 11750);

        $this->assertSame(400.0, (float) $fromAccount->fresh()->current_balance);
        $this->assertSame(21750.0, (float) $toAccount->fresh()->current_balance);
    }
}
