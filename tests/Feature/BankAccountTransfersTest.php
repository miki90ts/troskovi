<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BankAccountTransfersTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_is_rejected_when_source_account_has_insufficient_funds(): void
    {
        $user = User::factory()->create();
        $fromAccount = $user->bankAccounts()->create([
            'name' => 'Glavni račun',
            'bank_name' => 'Test banka',
            'account_number' => '1111222233334444',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 5000,
            'is_archived' => false,
        ]);
        $toAccount = $user->bankAccounts()->create([
            'name' => 'Štedni račun',
            'bank_name' => 'Test banka',
            'account_number' => '5555666677778888',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 12000,
            'is_archived' => false,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bank-accounts/transfer', [
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 10000,
            'description' => 'Prenos preko stanja',
            'date' => now()->toDateString(),
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('amount')
            ->assertJsonPath(
                'errors.amount.0',
                'Nema dovoljno sredstava na izvornom računu.',
            );

        $this->assertDatabaseCount('account_transfers', 0);
    }

    public function test_transfer_requires_different_destination_account_with_custom_message(): void
    {
        $user = User::factory()->create();
        $account = $user->bankAccounts()->create([
            'name' => 'Glavni račun',
            'bank_name' => 'Test banka',
            'account_number' => '1111222233334444',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 5000,
            'is_archived' => false,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bank-accounts/transfer', [
            'from_account_id' => $account->id,
            'to_account_id' => $account->id,
            'amount' => 1000,
            'description' => 'Nevalidan prenos',
            'date' => now()->toDateString(),
        ])
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.to_account_id.0',
                'Odredišni račun mora biti različit od izvornog računa.',
            );
    }

    public function test_transfer_updates_current_balance_for_both_accounts(): void
    {
        $user = User::factory()->create();
        $fromAccount = $user->bankAccounts()->create([
            'name' => 'Glavni račun',
            'bank_name' => 'Test banka',
            'account_number' => '1111222233334444',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 50000,
            'is_archived' => false,
        ]);
        $toAccount = $user->bankAccounts()->create([
            'name' => 'Štedni račun',
            'bank_name' => 'Test banka',
            'account_number' => '5555666677778888',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 12000,
            'is_archived' => false,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/bank-accounts/transfer', [
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 10000,
            'description' => 'Interni prenos',
            'date' => now()->toDateString(),
        ])->assertCreated();

        $this->assertSame(40000.0, (float) $fromAccount->fresh()->current_balance);
        $this->assertSame(22000.0, (float) $toAccount->fresh()->current_balance);
    }

    public function test_overview_uses_transfers_for_last_activity_date(): void
    {
        $user = User::factory()->create();
        $account = $user->bankAccounts()->create([
            'name' => 'Glavni račun',
            'bank_name' => 'Test banka',
            'account_number' => '1111222233334444',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 50000,
            'is_archived' => false,
        ]);
        $targetAccount = $user->bankAccounts()->create([
            'name' => 'Štedni račun',
            'bank_name' => 'Test banka',
            'account_number' => '5555666677778888',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 10000,
            'is_archived' => false,
        ]);

        $account->transactions()->create([
            'user_id' => $user->id,
            'type' => 'expense',
            'amount' => 1000,
            'date' => now()->subDays(3)->toDateString(),
            'description' => 'Starija transakcija',
            'payment_method' => 'bank_account',
        ]);

        $user->accountTransfers()->create([
            'from_account_id' => $account->id,
            'to_account_id' => $targetAccount->id,
            'amount' => 2500,
            'description' => 'Skoriji prenos',
            'date' => now()->subDay()->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/bank-accounts/{$account->id}/overview")
            ->assertOk()
            ->assertJsonPath(
                'data.last_transaction_date',
                now()->subDay()->toDateString(),
            );
    }
}
