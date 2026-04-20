<?php

namespace Tests\Feature;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\PaymentMethod;
use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Debt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecurringTransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_keeps_last_processed_date_null_until_the_rule_is_actually_processed(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 2500,
            'description' => 'Internet',
            'frequency' => 'monthly',
            'next_due_date' => '2026-05-15',
            'payment_method' => 'cash',
        ])
            ->assertCreated()
            ->assertJsonPath('data.next_due_date', '2026-05-15')
            ->assertJsonPath('data.last_processed_date', null);

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'description' => 'Internet',
            'next_due_date' => '2026-05-15 00:00:00',
        ]);

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'description' => 'Internet',
            'last_processed_date' => null,
        ]);
    }

    public function test_store_accepts_optional_debt_and_returns_linked_debt_payload(): void
    {
        $user = User::factory()->create();
        $debt = $this->createDebt($user, [
            'type' => DebtType::IOwe,
            'person_name' => 'Banka',
            'amount' => 240000,
            'remaining_amount' => 240000,
        ]);
        $bankAccount = $this->createBankAccount($user);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 10000,
            'description' => 'Rata kredita',
            'frequency' => 'monthly',
            'next_due_date' => '2026-05-15',
            'payment_method' => 'bank_account',
            'bank_account_id' => $bankAccount->id,
            'debt_id' => $debt->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.debt.id', $debt->id)
            ->assertJsonPath('data.debt.person_name', 'Banka')
            ->assertJsonPath('data.debt.type', DebtType::IOwe->value)
            ->assertJsonPath('data.debt.remaining_amount', 240000);

        $this->assertDatabaseHas('recurring_transactions', [
            'user_id' => $user->id,
            'description' => 'Rata kredita',
            'debt_id' => $debt->id,
        ]);
    }

    public function test_store_rejects_debt_that_belongs_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $foreignDebt = $this->createDebt($otherUser, [
            'person_name' => 'Tuđa banka',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 10000,
            'description' => 'Nevalidna rata',
            'frequency' => 'monthly',
            'next_due_date' => '2026-05-15',
            'payment_method' => 'cash',
            'debt_id' => $foreignDebt->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('debt_id');

        $this->assertDatabaseMissing('recurring_transactions', [
            'user_id' => $user->id,
            'description' => 'Nevalidna rata',
        ]);
    }

    public function test_store_rejects_category_and_bank_account_that_belong_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $foreignCategory = $otherUser->categories()->create([
            'name' => 'Tuđa kategorija',
            'type' => TransactionType::Expense,
            'icon' => null,
            'color' => null,
            'is_system' => false,
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

        $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 10000,
            'description' => 'Nevalidne reference',
            'frequency' => 'monthly',
            'next_due_date' => '2026-05-15',
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
            'type' => TransactionType::Expense,
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

        $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 10000,
            'description' => 'Validne reference',
            'frequency' => 'monthly',
            'next_due_date' => '2026-05-15',
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

        $recurring = $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 2500,
            'description' => 'Internet',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->addWeek()->toDateString(),
            'payment_method' => PaymentMethod::BankAccount,
            'category_id' => $ownCategory->id,
            'bank_account_id' => $ownBankAccount->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/recurring-transactions/{$recurring->id}", [
            'category_id' => $foreignCategory->id,
            'bank_account_id' => $foreignBankAccount->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'bank_account_id']);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
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

        $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 10000,
            'description' => 'Nevalidan račun za keš',
            'frequency' => 'monthly',
            'next_due_date' => '2026-05-15',
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

        $this->postJson('/api/v1/recurring-transactions', [
            'type' => 'expense',
            'amount' => 10000,
            'description' => 'Nedostaje račun',
            'frequency' => 'monthly',
            'next_due_date' => '2026-05-15',
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

        $recurring = $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 2500,
            'description' => 'Internet',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->addWeek()->toDateString(),
            'payment_method' => PaymentMethod::BankAccount,
            'bank_account_id' => $bankAccount->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/recurring-transactions/{$recurring->id}", [
            'payment_method' => 'cash',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('bank_account_id');
    }

    public function test_users_can_deactivate_and_reactivate_recurring_transactions_with_partial_updates(): void
    {
        $user = User::factory()->create();
        $recurring = $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 2500,
            'description' => 'Internet',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->addWeek()->toDateString(),
            'last_processed_date' => now()->subWeeks(3)->toDateString(),
            'payment_method' => PaymentMethod::Cash,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/recurring-transactions/{$recurring->id}", [
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false)
            ->assertJsonPath('data.can_delete', true);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'is_active' => false,
        ]);

        $this->putJson("/api/v1/recurring-transactions/{$recurring->id}", [
            'is_active' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.can_delete', true);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'is_active' => true,
        ]);
    }

    public function test_updating_execution_date_or_frequency_does_not_overwrite_last_processed_date(): void
    {
        $user = User::factory()->create();
        $recurring = $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 4300,
            'description' => 'Zakup',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => '2026-05-21',
            'last_processed_date' => '2026-04-21',
            'payment_method' => PaymentMethod::Cash,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/recurring-transactions/{$recurring->id}", [
            'frequency' => 'weekly',
            'next_due_date' => '2026-05-21',
        ])
            ->assertOk()
            ->assertJsonPath('data.next_due_date', '2026-05-21')
            ->assertJsonPath('data.last_processed_date', '2026-04-21');

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'frequency' => 'weekly',
            'next_due_date' => '2026-05-21 00:00:00',
            'last_processed_date' => '2026-04-21 00:00:00',
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

        $recurring = $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 2500,
            'description' => 'Internet',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->addWeek()->toDateString(),
            'payment_method' => PaymentMethod::Cash,
            'debt_id' => $ownDebt->id,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/recurring-transactions/{$recurring->id}", [
            'debt_id' => $foreignDebt->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('debt_id');

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
            'debt_id' => $ownDebt->id,
        ]);
    }

    public function test_users_can_delete_recurring_transactions_without_linked_transactions(): void
    {
        $user = User::factory()->create();
        $recurring = $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 1800,
            'description' => 'Streaming',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->addDays(10)->toDateString(),
            'payment_method' => PaymentMethod::Cash,
            'is_active' => false,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/recurring-transactions/{$recurring->id}")
            ->assertOk();

        $this->assertDatabaseMissing('recurring_transactions', [
            'id' => $recurring->id,
        ]);
    }

    public function test_users_cannot_delete_recurring_transactions_with_linked_transactions(): void
    {
        $user = User::factory()->create();
        $recurring = $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 3200,
            'description' => 'Kirija',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->addDays(5)->toDateString(),
            'payment_method' => PaymentMethod::Cash,
            'is_active' => true,
        ]);

        $user->transactions()->create([
            'recurring_transaction_id' => $recurring->id,
            'type' => TransactionType::Expense,
            'amount' => 3200,
            'date' => now()->toDateString(),
            'description' => 'Kirija za april',
            'payment_method' => PaymentMethod::Cash,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/recurring-transactions/{$recurring->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors('recurring_transaction');

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $recurring->id,
        ]);
    }

    public function test_processing_due_recurring_transactions_updates_linked_debts_for_all_supported_flows(): void
    {
        $user = User::factory()->create();

        $iOweReducingDebt = $this->createDebt($user, [
            'type' => DebtType::IOwe,
            'person_name' => 'Banka A',
            'amount' => 100000,
            'remaining_amount' => 100000,
        ]);
        $iOweIncreasingDebt = $this->createDebt($user, [
            'type' => DebtType::IOwe,
            'person_name' => 'Banka B',
            'amount' => 100000,
            'remaining_amount' => 100000,
        ]);
        $owedToMeReducingDebt = $this->createDebt($user, [
            'type' => DebtType::OwedToMe,
            'person_name' => 'Marko',
            'amount' => 50000,
            'remaining_amount' => 50000,
        ]);
        $owedToMeIncreasingDebt = $this->createDebt($user, [
            'type' => DebtType::OwedToMe,
            'person_name' => 'Jelena',
            'amount' => 50000,
            'remaining_amount' => 50000,
        ]);

        $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 10000,
            'description' => 'Rata kredita',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->subDay()->toDateString(),
            'payment_method' => PaymentMethod::BankAccount,
            'debt_id' => $iOweReducingDebt->id,
            'is_active' => true,
        ]);

        $user->recurringTransactions()->create([
            'type' => TransactionType::Income,
            'amount' => 5000,
            'description' => 'Novo zaduženje',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->subDay()->toDateString(),
            'payment_method' => PaymentMethod::Cash,
            'debt_id' => $iOweIncreasingDebt->id,
            'is_active' => true,
        ]);

        $user->recurringTransactions()->create([
            'type' => TransactionType::Income,
            'amount' => 7000,
            'description' => 'Povraćaj pozajmice',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->subDay()->toDateString(),
            'payment_method' => PaymentMethod::Cash,
            'debt_id' => $owedToMeReducingDebt->id,
            'is_active' => true,
        ]);

        $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 3000,
            'description' => 'Nova pozajmica',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->subDay()->toDateString(),
            'payment_method' => PaymentMethod::Cash,
            'debt_id' => $owedToMeIncreasingDebt->id,
            'is_active' => true,
        ]);

        $this->artisan('transactions:process-recurring')
            ->assertSuccessful();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Rata kredita',
            'debt_id' => $iOweReducingDebt->id,
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Novo zaduženje',
            'debt_id' => $iOweIncreasingDebt->id,
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Povraćaj pozajmice',
            'debt_id' => $owedToMeReducingDebt->id,
        ]);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Nova pozajmica',
            'debt_id' => $owedToMeIncreasingDebt->id,
        ]);

        $this->assertEquals(90000.0, (float) $iOweReducingDebt->fresh()->remaining_amount);
        $this->assertEquals(105000.0, (float) $iOweIncreasingDebt->fresh()->remaining_amount);
        $this->assertEquals(43000.0, (float) $owedToMeReducingDebt->fresh()->remaining_amount);
        $this->assertEquals(53000.0, (float) $owedToMeIncreasingDebt->fresh()->remaining_amount);
    }

    public function test_updating_recurring_debt_affects_only_future_generated_transactions(): void
    {
        $user = User::factory()->create();
        $oldDebt = $this->createDebt($user, [
            'type' => DebtType::IOwe,
            'person_name' => 'Banka A',
            'amount' => 100000,
            'remaining_amount' => 100000,
        ]);
        $newDebt = $this->createDebt($user, [
            'type' => DebtType::IOwe,
            'person_name' => 'Banka B',
            'amount' => 80000,
            'remaining_amount' => 80000,
        ]);
        $bankAccount = $this->createBankAccount($user);

        $recurring = $user->recurringTransactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 10000,
            'description' => 'Rata kredita',
            'frequency' => RecurringFrequency::Monthly,
            'next_due_date' => now()->subDay()->toDateString(),
            'payment_method' => PaymentMethod::BankAccount,
            'bank_account_id' => $bankAccount->id,
            'debt_id' => $oldDebt->id,
            'is_active' => true,
        ]);

        $this->artisan('transactions:process-recurring')
            ->assertSuccessful();

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/recurring-transactions/{$recurring->id}", [
            'debt_id' => $newDebt->id,
            'next_due_date' => now()->subDay()->toDateString(),
            'frequency' => RecurringFrequency::Monthly->value,
        ])->assertOk();

        $this->artisan('transactions:process-recurring')
            ->assertSuccessful();

        $linkedTransactions = $user->transactions()
            ->where('recurring_transaction_id', $recurring->id)
            ->orderBy('date')
            ->get();

        $this->assertCount(2, $linkedTransactions);
        $this->assertSame($oldDebt->id, $linkedTransactions[0]->debt_id);
        $this->assertSame($newDebt->id, $linkedTransactions[1]->debt_id);
        $this->assertEquals(90000.0, (float) $oldDebt->fresh()->remaining_amount);
        $this->assertEquals(70000.0, (float) $newDebt->fresh()->remaining_amount);
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

    private function createBankAccount(User $user, array $overrides = []): BankAccount
    {
        return $user->bankAccounts()->create(array_merge([
            'name' => 'Moj račun',
            'bank_name' => 'Test banka',
            'account_number' => '1234567890123456',
            'currency' => 'RSD',
            'color' => null,
            'icon' => null,
            'initial_balance' => 0,
            'is_archived' => false,
        ], $overrides));
    }
}
