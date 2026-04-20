<?php

namespace Tests\Feature;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Debt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_rejects_debt_that_belongs_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $foreignDebt = $this->createDebt($otherUser, [
            'person_name' => 'Tuđa banka',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Nevalidna transakcija',
            'payment_method' => 'cash',
            'debt_id' => $foreignDebt->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('debt_id');

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'description' => 'Nevalidna transakcija',
        ]);
    }

    public function test_update_rejects_debt_that_belongs_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownDebt = $this->createDebt($user, [
            'person_name' => 'Moja banka',
        ]);
        $foreignDebt = $this->createDebt($otherUser, [
            'person_name' => 'Tuđa banka',
        ]);

        $transaction = $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Moja transakcija',
            'payment_method' => PaymentMethod::Cash,
            'debt_id' => $ownDebt->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/transactions/{$transaction->id}", [
            'debt_id' => $foreignDebt->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('debt_id');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'debt_id' => $ownDebt->id,
        ]);
    }

    public function test_store_rejects_category_and_bank_account_that_belong_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $foreignCategory = $this->createCategory($otherUser, [
            'name' => 'Tuđa kategorija',
        ]);
        $foreignBankAccount = $otherUser->bankAccounts()->create([
            'name' => 'Tuđ račun',
            'bank_name' => 'Test banka',
            'account_number' => '1234567890123456',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 0,
            'is_archived' => false,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Nevalidne reference',
            'payment_method' => 'bank_account',
            'category_id' => $foreignCategory->id,
            'bank_account_id' => $foreignBankAccount->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'bank_account_id']);
    }

    public function test_store_accepts_system_category_and_owned_bank_account(): void
    {
        $user = User::factory()->create();
        $systemCategory = $this->createCategory(null, [
            'name' => 'Sistemska kategorija',
            'is_system' => true,
        ]);
        $bankAccount = $user->bankAccounts()->create([
            'name' => 'Moj račun',
            'bank_name' => 'Test banka',
            'account_number' => '1234567890123456',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 0,
            'is_archived' => false,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Validne reference',
            'payment_method' => 'bank_account',
            'category_id' => $systemCategory->id,
            'bank_account_id' => $bankAccount->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.category.id', $systemCategory->id)
            ->assertJsonPath('data.bank_account.id', $bankAccount->id);
    }

    public function test_update_rejects_category_and_bank_account_that_belong_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownCategory = $this->createCategory($user, [
            'name' => 'Moja kategorija',
        ]);
        $foreignCategory = $this->createCategory($otherUser, [
            'name' => 'Tuđa kategorija',
        ]);
        $ownBankAccount = $user->bankAccounts()->create([
            'name' => 'Moj račun',
            'bank_name' => 'Test banka',
            'account_number' => '1234567890123456',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 0,
            'is_archived' => false,
        ]);
        $foreignBankAccount = $otherUser->bankAccounts()->create([
            'name' => 'Tuđ račun',
            'bank_name' => 'Test banka',
            'account_number' => '6543210987654321',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 0,
            'is_archived' => false,
        ]);

        $transaction = $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Moja transakcija',
            'payment_method' => PaymentMethod::BankAccount,
            'category_id' => $ownCategory->id,
            'bank_account_id' => $ownBankAccount->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/transactions/{$transaction->id}", [
            'category_id' => $foreignCategory->id,
            'bank_account_id' => $foreignBankAccount->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'bank_account_id']);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => $ownCategory->id,
            'bank_account_id' => $ownBankAccount->id,
        ]);
    }

    public function test_store_rejects_bank_account_when_payment_method_is_not_bank_account(): void
    {
        $user = User::factory()->create();
        $bankAccount = $user->bankAccounts()->create([
            'name' => 'Moj račun',
            'bank_name' => 'Test banka',
            'account_number' => '1234567890123456',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 0,
            'is_archived' => false,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Nevalidan račun za keš',
            'payment_method' => 'cash',
            'bank_account_id' => $bankAccount->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('bank_account_id');
    }

    public function test_store_requires_bank_account_when_payment_method_is_bank_account(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Nedostaje račun',
            'payment_method' => 'bank_account',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('bank_account_id');
    }

    public function test_update_rejects_switching_payment_method_to_cash_without_clearing_bank_account(): void
    {
        $user = User::factory()->create();
        $bankAccount = $user->bankAccounts()->create([
            'name' => 'Moj račun',
            'bank_name' => 'Test banka',
            'account_number' => '1234567890123456',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 0,
            'is_archived' => false,
        ]);

        $transaction = $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Moja transakcija',
            'payment_method' => PaymentMethod::BankAccount,
            'bank_account_id' => $bankAccount->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/transactions/{$transaction->id}", [
            'payment_method' => 'cash',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('bank_account_id');
    }

    private function createDebt(User $user, array $overrides = []): Debt
    {
        return $user->debts()->create(array_merge([
            'type' => DebtType::IOwe,
            'person_name' => 'Test osoba',
            'description' => 'Test dugovanje',
            'amount' => 100000,
            'remaining_amount' => 100000,
            'date' => now()->toDateString(),
            'due_date' => now()->addMonth()->toDateString(),
            'status' => DebtStatus::Active,
            'notes' => null,
        ], $overrides));
    }

    private function createCategory(?User $user, array $overrides = []): Category
    {
        return Category::create(array_merge([
            'user_id' => $user?->id,
            'name' => 'Test kategorija',
            'type' => TransactionType::Expense,
            'icon' => null,
            'color' => null,
            'is_system' => false,
        ], $overrides));
    }
}
