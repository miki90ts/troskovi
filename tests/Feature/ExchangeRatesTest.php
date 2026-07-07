<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExchangeRatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_users_can_visit_exchange_rates_settings_page(): void
    {
        $user = User::factory()->create();

        $this->withoutVite();

        $this->actingAs($user)
            ->get(route('exchange-rates.edit'))
            ->assertOk();
    }

    public function test_users_can_create_list_update_and_delete_exchange_rates(): void
    {
        $user = User::factory()->create();
        $eur = Currency::query()->where('iso_code', 'EUR')->firstOrFail();

        Sanctum::actingAs($user);

        $created = $this->postJson('/api/v1/exchange-rates', [
            'currency_id' => $eur->id,
            'date' => '2026-07-01',
            'rate' => 117.25,
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.currency.iso_code', 'EUR')
            ->assertJsonPath('data.rate', 117.25);

        $updated = $this->postJson('/api/v1/exchange-rates', [
            'currency_id' => $eur->id,
            'date' => '2026-07-01',
            'rate' => 118.1,
        ]);

        $updated->assertCreated()
            ->assertJsonPath('data.rate', 118.1);

        $rateId = $updated->json('data.id');

        $this->getJson('/api/v1/exchange-rates')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/v1/exchange-rates/{$rateId}")
            ->assertOk();

        $this->assertDatabaseCount('exchange_rates', 0);
    }

    public function test_cash_transactions_use_previous_exchange_rate_when_exact_date_is_missing(): void
    {
        $user = User::factory()->create();
        $eur = Currency::query()->where('iso_code', 'EUR')->firstOrFail();

        $user->forceFill(['default_currency_id' => $eur->id])->save();

        $eur->exchangeRates()->create([
            'date' => '2026-07-01',
            'rate' => 117.5,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 100,
            'date' => '2026-07-03',
            'description' => 'Kupovina u EUR',
            'payment_method' => 'cash',
        ])
            ->assertCreated()
            ->assertJsonPath('data.exchange_rate', 117.5)
            ->assertJsonPath('data.base_amount', 11750);
    }

    public function test_transaction_rate_lookup_uses_new_exchange_rate_after_rate_changes(): void
    {
        $user = User::factory()->create();
        $eur = Currency::query()->where('iso_code', 'EUR')->firstOrFail();

        $user->forceFill(['default_currency_id' => $eur->id])->save();

        $eur->exchangeRates()->create([
            'date' => '2026-07-01',
            'rate' => 117.5,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 100,
            'date' => '2026-07-03',
            'description' => 'Prvi lookup',
            'payment_method' => 'cash',
        ])->assertCreated()->assertJsonPath('data.exchange_rate', 117.5);

        $this->postJson('/api/v1/exchange-rates', [
            'currency_id' => $eur->id,
            'date' => '2026-07-02',
            'rate' => 118.25,
        ])->assertCreated();

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 100,
            'date' => '2026-07-03',
            'description' => 'Drugi lookup',
            'payment_method' => 'cash',
        ])
            ->assertCreated()
            ->assertJsonPath('data.exchange_rate', 118.25)
            ->assertJsonPath('data.base_amount', 11825);
    }

    public function test_transactions_are_rejected_when_no_exchange_rate_exists_for_selected_currency(): void
    {
        $user = User::factory()->create();
        $eur = Currency::query()->where('iso_code', 'EUR')->firstOrFail();

        $user->forceFill(['default_currency_id' => $eur->id])->save();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 100,
            'date' => '2026-07-03',
            'description' => 'Kupovina bez kursa',
            'payment_method' => 'cash',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('date');
    }
}
