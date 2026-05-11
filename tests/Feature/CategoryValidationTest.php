<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_returns_custom_validation_messages_for_categories(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/categories', [
            'name' => '',
            'type' => 'invalid',
            'icon' => str_repeat('a', 51),
            'color' => '#1234567',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.name.0', 'Naziv kategorije je obavezan.')
            ->assertJsonPath('errors.type.0', 'Izabrani tip kategorije nije ispravan.')
            ->assertJsonPath('errors.icon.0', 'Ikonica ne sme biti duža od 50 karaktera.')
            ->assertJsonPath('errors.color.0', 'Boja ne sme biti duža od 7 karaktera.');
    }
}
