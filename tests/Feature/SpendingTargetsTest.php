<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SpendingTargetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_users_can_visit_the_budgets_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('budgets.edit'))
            ->assertOk();
    }

    public function test_users_can_create_update_list_and_delete_spending_targets(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create([
            'name' => 'Hrana',
            'type' => TransactionType::Expense,
            'icon' => 'utensils',
            'color' => '#14b8a6',
            'is_system' => false,
        ]);

        Sanctum::actingAs($user);

        $created = $this->postJson('/api/v1/spending-targets', [
            'period' => 'monthly',
            'target_amount' => 25000,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $created->assertCreated()
            ->assertJsonPath('data.period', 'monthly')
            ->assertJsonPath('data.scope', 'category')
            ->assertJsonPath('data.category.name', 'Hrana');

        $targetId = $created->json('data.id');

        $this->getJson('/api/v1/spending-targets')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->putJson("/api/v1/spending-targets/{$targetId}", [
            'period' => 'monthly',
            'target_amount' => 30000,
            'category_id' => $category->id,
            'is_active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.target_amount', 30000)
            ->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/v1/spending-targets/{$targetId}")
            ->assertOk();

        $this->assertDatabaseCount('spending_targets', 0);
    }

    public function test_users_cannot_create_duplicate_spending_targets_for_the_same_period_and_scope(): void
    {
        $user = User::factory()->create();

        $user->spendingTargets()->create([
            'period' => 'monthly',
            'target_amount' => 40000,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/spending-targets', [
            'period' => 'monthly',
            'target_amount' => 45000,
            'is_active' => true,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_users_cannot_manage_spending_targets_that_belong_to_another_user(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $target = $owner->spendingTargets()->create([
            'period' => 'weekly',
            'target_amount' => 12000,
            'is_active' => true,
        ]);

        Sanctum::actingAs($intruder);

        $this->putJson("/api/v1/spending-targets/{$target->id}", [
            'period' => 'weekly',
            'target_amount' => 15000,
            'is_active' => true,
        ])->assertForbidden();

        $this->deleteJson("/api/v1/spending-targets/{$target->id}")
            ->assertForbidden();
    }

    public function test_spending_progress_returns_overall_and_category_statuses_for_the_selected_period(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->create([
            'name' => 'Namirnice',
            'type' => TransactionType::Expense,
            'icon' => 'shopping-cart',
            'color' => '#f97316',
            'is_system' => false,
        ]);

        $user->spendingTargets()->create([
            'period' => 'monthly',
            'target_amount' => 1000,
            'is_active' => true,
        ]);

        $user->spendingTargets()->create([
            'period' => 'monthly',
            'target_amount' => 200,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $user->transactions()->create([
            'type' => TransactionType::Expense,
            'amount' => 1100,
            'date' => now()->toDateString(),
            'description' => 'Velika kupovina',
            'payment_method' => PaymentMethod::Cash,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/reports/spending-progress?period=monthly');

        $response->assertOk()
            ->assertJsonPath('data.period', 'monthly')
            ->assertJsonPath('data.summary.active_count', 2)
            ->assertJsonPath('data.summary.exceeded_count', 2)
            ->assertJsonPath('data.overall_target.status', 'exceeded')
            ->assertJsonPath('data.top_risk_target.status', 'exceeded');

        $this->assertCount(2, $response->json('data.targets'));
    }
}
