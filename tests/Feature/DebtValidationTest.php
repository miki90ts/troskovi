<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DebtValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_custom_validation_messages_for_debts(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/debts', [
            'type' => 'i_owe',
            'person_name' => '',
            'description' => '',
            'amount' => 0,
            'date' => 'invalid-date',
            'due_date' => '2024-01-01',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.person_name.0', 'Ime osobe je obavezno.')
            ->assertJsonPath('errors.description.0', 'Opis je obavezan.')
            ->assertJsonPath('errors.amount.0', 'Iznos mora biti veći od 0.')
            ->assertJsonPath('errors.date.0', 'Datum nije ispravan.');
    }

    public function test_store_rejects_due_date_before_date_with_custom_message(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/debts', [
            'type' => 'owed_to_me',
            'person_name' => 'Test osoba',
            'description' => 'Test opis',
            'amount' => 1200,
            'date' => '2026-05-11',
            'due_date' => '2026-05-10',
        ])
            ->assertStatus(422)
            ->assertJsonPath(
                'errors.due_date.0',
                'Rok dospeća mora biti isti ili nakon datuma zaduženja.',
            );
    }
}
