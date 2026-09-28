<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Http\Resources\TransactionResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WarrantyReceiptLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_link_uses_the_current_origin_and_works_with_a_web_session(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $transaction = $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 2500,
            'date' => now()->toDateString(),
            'description' => 'Garancijski racun',
            'payment_method' => PaymentMethod::Cash,
            'receipt_path' => "receipts/{$user->id}/warranty.jpg",
            'is_warranty' => true,
            'warranty_expires_at' => now()->addYear()->toDateString(),
        ]);

        Storage::disk('local')->put($transaction->receipt_path, 'image contents');

        $receiptUrl = (new TransactionResource($transaction))->resolve(request())['receipt_url'];

        $this->assertSame("/transactions/{$transaction->id}/receipt", $receiptUrl);

        $this->actingAs($user)
            ->get("{$receiptUrl}?preview=1")
            ->assertOk();

        $this->actingAs($user)
            ->get($receiptUrl)
            ->assertOk()
            ->assertDownload('warranty.jpg');
    }
}
