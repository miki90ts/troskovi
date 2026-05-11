<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoyaltyCardValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_custom_validation_messages_for_loyalty_cards(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/loyalty-cards', [
            'name' => '',
            'card_number' => '',
            'notes' => str_repeat('n', 1001),
            'color' => '#1234567',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'Naziv kartice je obavezan.')
            ->assertJsonPath('errors.card_number.0', 'Broj kartice je obavezan.')
            ->assertJsonPath('errors.notes.0', 'Napomena ne sme biti duža od 1000 karaktera.')
            ->assertJsonPath('errors.color.0', 'Boja ne sme biti duža od 7 karaktera.');
    }
}
