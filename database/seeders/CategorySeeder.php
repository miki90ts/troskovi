<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $expenseCategories = [
            ['name' => 'Food & Dining', 'icon' => 'utensils', 'color' => '#ef4444'],
            ['name' => 'Transportation', 'icon' => 'car', 'color' => '#f97316'],
            ['name' => 'Housing', 'icon' => 'home', 'color' => '#eab308'],
            ['name' => 'Utilities', 'icon' => 'zap', 'color' => '#84cc16'],
            ['name' => 'Entertainment', 'icon' => 'film', 'color' => '#06b6d4'],
            ['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => '#8b5cf6'],
            ['name' => 'Healthcare', 'icon' => 'heart-pulse', 'color' => '#ec4899'],
            ['name' => 'Education', 'icon' => 'graduation-cap', 'color' => '#14b8a6'],
            ['name' => 'Personal Care', 'icon' => 'sparkles', 'color' => '#f43f5e'],
            ['name' => 'Other', 'icon' => 'circle-dot', 'color' => '#6b7280'],
        ];

        $incomeCategories = [
            ['name' => 'Salary', 'icon' => 'briefcase', 'color' => '#22c55e'],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#3b82f6'],
            ['name' => 'Investment', 'icon' => 'trending-up', 'color' => '#a855f7'],
            ['name' => 'Gift', 'icon' => 'gift', 'color' => '#f59e0b'],
            ['name' => 'Other', 'icon' => 'circle-dot', 'color' => '#6b7280'],
        ];

        foreach ($expenseCategories as $category) {
            Category::create(array_merge($category, [
                'type' => 'expense',
                'is_system' => true,
                'user_id' => null,
            ]));
        }

        foreach ($incomeCategories as $category) {
            Category::create(array_merge($category, [
                'type' => 'income',
                'is_system' => true,
                'user_id' => null,
            ]));
        }
    }
}
