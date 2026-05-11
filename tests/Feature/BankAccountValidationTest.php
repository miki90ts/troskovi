<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BankAccountValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_custom_validation_messages_for_bank_accounts(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bank-accounts', [
            'name' => '',
            'bank_name' => '',
            'currency' => '',
            'initial_balance' => -10,
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'Naziv računa je obavezan.')
            ->assertJsonPath('errors.bank_name.0', 'Naziv banke je obavezan.')
            ->assertJsonPath('errors.currency.0', 'Valuta je obavezna.')
            ->assertJsonPath(
                'errors.initial_balance.0',
                'Početno stanje ne sme biti negativno.',
            );
    }

    public function test_store_rejects_too_long_account_number_with_custom_message(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bank-accounts', [
            'name' => 'Tekući račun',
            'bank_name' => 'Test banka',
            'account_number' => str_repeat('1', 51),
            'currency' => 'RSD',
            'initial_balance' => 0,
        ])
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.account_number.0',
                'Broj računa ne sme biti duži od 50 karaktera.',
            );
    }
}
