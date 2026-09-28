<?php

namespace Tests\Feature;

use App\Enums\DebtStatus;
use App\Enums\DebtType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_and_update_fiscal_verification_url(): void
    {
        $user = User::factory()->create();
        $url = 'https://suf.purs.gov.rs/v/?vl=valid-payload';

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 1299.99,
            'date' => now()->toDateString(),
            'description' => 'Fiskalni račun',
            'payment_method' => 'cash',
            'receipt_verification_url' => $url,
        ])
            ->assertCreated()
            ->assertJsonPath('data.receipt_verification_url', $url);

        $transactionId = $response->json('data.id');
        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'receipt_verification_url' => $url,
        ]);

        $this->putJson("/api/v1/transactions/{$transactionId}", [
            'receipt_verification_url' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.receipt_verification_url', null);

        $this->assertDatabaseHas('transactions', [
            'id' => $transactionId,
            'receipt_verification_url' => null,
        ]);
    }

    public function test_store_rejects_untrusted_fiscal_verification_urls(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $invalidUrls = [
            'http://suf.purs.gov.rs/v/?vl=payload',
            'https://suf.purs.gov.rs.evil.example/v/?vl=payload',
            'https://suf.purs.gov.rs/other/?vl=payload',
            'https://suf.purs.gov.rs/v/',
            'https://user:pass@suf.purs.gov.rs/v/?vl=payload',
            'https://suf.purs.gov.rs/v/?vl=payload#fragment',
            'https://suf.purs.gov.rs/v/?vl='.str_repeat('x', 8192),
        ];

        foreach ($invalidUrls as $index => $invalidUrl) {
            $this->postJson('/api/v1/transactions', [
                'type' => 'expense',
                'amount' => 100,
                'date' => now()->toDateString(),
                'description' => "Nevažeći QR {$index}",
                'payment_method' => 'cash',
                'receipt_verification_url' => $invalidUrl,
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('receipt_verification_url');
        }
    }

    public function test_another_user_cannot_change_or_open_a_transactions_receipt(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $transaction = $owner->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 1000,
            'date' => now()->toDateString(),
            'description' => 'Privatni račun',
            'payment_method' => PaymentMethod::Cash,
            'receipt_path' => 'receipts/private.jpg',
            'receipt_verification_url' => 'https://suf.purs.gov.rs/v/?vl=private',
        ]);
        Storage::disk('local')->put($transaction->receipt_path, 'private');

        Sanctum::actingAs($otherUser);

        $this->putJson("/api/v1/transactions/{$transaction->id}", [
            'receipt_verification_url' => null,
        ])->assertForbidden();
        $this->get("/api/v1/transactions/{$transaction->id}/receipt?preview=1")
            ->assertForbidden();
    }

    public function test_phone_image_upload_is_private_and_uses_a_matching_extension(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/transactions', [
            'type' => 'expense',
            'amount' => 2500,
            'date' => now()->toDateString(),
            'description' => 'Garancijski račun',
            'payment_method' => 'cash',
            'is_warranty' => true,
            'receipt' => UploadedFile::fake()->image('camera-upload.png', 1600, 1200)->size(4000),
        ])->assertCreated();

        $transaction = Transaction::findOrFail($response->json('data.id'));
        Storage::disk('local')->assertExists($transaction->receipt_path);

        if (extension_loaded('gd')) {
            $this->assertStringEndsWith('.jpg', $transaction->receipt_path);
        }

        $this->get("/api/v1/transactions/{$transaction->id}/receipt?preview=1")
            ->assertOk();
    }

    public function test_receipt_upload_rejects_oversized_and_non_image_files(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $base = [
            'type' => 'expense',
            'amount' => 500,
            'date' => now()->toDateString(),
            'description' => 'Nevažeća slika',
            'payment_method' => 'cash',
            'is_warranty' => true,
        ];

        $this->withHeader('Accept', 'application/json')
            ->post('/api/v1/transactions', $base + [
                'receipt' => UploadedFile::fake()->image('large.jpg')->size(5121),
            ])
            ->assertUnprocessable()
            ->assertInvalid('receipt');

        $this->withHeader('Accept', 'application/json')
            ->post('/api/v1/transactions', $base + [
                'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertInvalid('receipt');
    }

    public function test_expired_warranty_cleanup_deletes_only_the_image_and_keeps_the_qr_url(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $receiptPath = 'receipts/expired.jpg';
        $url = 'https://suf.purs.gov.rs/v/?vl=expired-warranty';
        $transaction = $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 3000,
            'date' => now()->subYears(3)->toDateString(),
            'description' => 'Istekla garancija',
            'payment_method' => PaymentMethod::Cash,
            'is_warranty' => true,
            'warranty_expires_at' => now()->subYear()->toDateString(),
            'receipt_path' => $receiptPath,
            'receipt_verification_url' => $url,
        ]);
        Storage::disk('local')->put($receiptPath, 'expired');

        $this->artisan('warranties:cleanup-expired')->assertSuccessful();

        Storage::disk('local')->assertMissing($receiptPath);
        $transaction->refresh();
        $this->assertNull($transaction->receipt_path);
        $this->assertSame($url, $transaction->receipt_verification_url);
    }

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
