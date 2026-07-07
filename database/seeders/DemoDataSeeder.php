<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            return;
        }

        $rsd = Currency::query()->where('iso_code', 'RSD')->first();

        if (! $rsd) {
            return;
        }

        $user->forceFill([
            'default_currency_id' => $user->default_currency_id ?? $rsd->id,
        ])->save();

        // Create bank accounts
        $checking = BankAccount::create([
            'user_id' => $user->id,
            'name' => 'Main Checking',
            'bank_name' => 'Chase',
            'account_number' => '4521789034',
            'currency' => 'RSD',
            'currency_id' => $rsd->id,
            'color' => '#3b82f6',
            'icon' => 'landmark',
            'initial_balance' => 5000.00,
        ]);

        $savings = BankAccount::create([
            'user_id' => $user->id,
            'name' => 'Savings Account',
            'bank_name' => 'Wells Fargo',
            'account_number' => '7891234567',
            'currency' => 'RSD',
            'currency_id' => $rsd->id,
            'color' => '#22c55e',
            'icon' => 'piggy-bank',
            'initial_balance' => 15000.00,
        ]);

        $credit = BankAccount::create([
            'user_id' => $user->id,
            'name' => 'Business Account',
            'bank_name' => 'Bank of America',
            'account_number' => '3456789012',
            'currency' => 'RSD',
            'currency_id' => $rsd->id,
            'color' => '#f59e0b',
            'icon' => 'building',
            'initial_balance' => 8500.00,
        ]);

        $expenseCategories = Category::where('type', 'expense')->where('is_system', true)->get();
        $incomeCategories = Category::where('type', 'income')->where('is_system', true)->get();

        $accounts = [$checking, $savings, $credit];
        $now = CarbonImmutable::now();

        // Generate 6 months of transactions
        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $monthStart = $now->subMonths($monthsAgo)->startOfMonth();

            // Income: Salary (1st of each month)
            $salaryCategory = $incomeCategories->where('name', 'Salary')->first();
            $salaryAmount = fake()->randomFloat(2, 4500, 5500);
            Transaction::create([
                'user_id' => $user->id,
                'bank_account_id' => $checking->id,
                'category_id' => $salaryCategory->id,
                'type' => 'income',
                'amount' => $salaryAmount,
                'currency_id' => $rsd->id,
                'exchange_rate' => 1,
                'base_amount' => $salaryAmount,
                'date' => $monthStart->addDay(),
                'description' => 'Monthly Salary',
                'payment_method' => 'bank_account',
            ]);

            // Income: Freelance (occasional)
            if (fake()->boolean(60)) {
                $freelanceCategory = $incomeCategories->where('name', 'Freelance')->first();
                $freelanceAmount = fake()->randomFloat(2, 500, 2000);
                Transaction::create([
                    'user_id' => $user->id,
                    'bank_account_id' => $credit->id,
                    'category_id' => $freelanceCategory->id,
                    'type' => 'income',
                    'amount' => $freelanceAmount,
                    'currency_id' => $rsd->id,
                    'exchange_rate' => 1,
                    'base_amount' => $freelanceAmount,
                    'date' => $monthStart->addDays(fake()->numberBetween(5, 25)),
                    'description' => fake()->randomElement(['Web Design Project', 'Consulting Fee', 'Logo Design', 'App Development']),
                    'payment_method' => 'bank_account',
                ]);
            }

            // Expenses: Multiple per month
            $expenseCount = fake()->numberBetween(10, 18);

            for ($i = 0; $i < $expenseCount; $i++) {
                $category = $expenseCategories->random();
                $useBank = fake()->boolean(70);
                $account = $useBank ? fake()->randomElement($accounts) : null;

                $amounts = match ($category->name) {
                    'Food & Dining' => [8, 75],
                    'Transportation' => [5, 80],
                    'Housing' => [800, 1500],
                    'Utilities' => [30, 200],
                    'Entertainment' => [10, 100],
                    'Shopping' => [15, 300],
                    'Healthcare' => [20, 500],
                    'Education' => [10, 200],
                    'Personal Care' => [10, 80],
                    default => [5, 100],
                };

                $descriptions = match ($category->name) {
                    'Food & Dining' => ['Grocery Store', 'Restaurant Dinner', 'Coffee Shop', 'Fast Food', 'Pizza Delivery', 'Bakery'],
                    'Transportation' => ['Gas Station', 'Uber Ride', 'Bus Pass', 'Parking Fee', 'Car Wash'],
                    'Housing' => ['Monthly Rent', 'Home Repair', 'Cleaning Service'],
                    'Utilities' => ['Electricity Bill', 'Water Bill', 'Internet Bill', 'Phone Bill', 'Gas Bill'],
                    'Entertainment' => ['Movie Tickets', 'Concert', 'Streaming Service', 'Book Purchase', 'Game'],
                    'Shopping' => ['Amazon Order', 'Clothing Store', 'Electronics', 'Home Decor', 'Shoes'],
                    'Healthcare' => ['Pharmacy', 'Doctor Visit', 'Dental Checkup', 'Vitamins'],
                    'Education' => ['Online Course', 'Books', 'Workshop', 'Certification'],
                    'Personal Care' => ['Haircut', 'Gym Membership', 'Skin Care', 'Spa'],
                    default => ['Miscellaneous Purchase', 'General Expense'],
                };

                $expenseAmount = fake()->randomFloat(2, $amounts[0], $amounts[1]);

                Transaction::create([
                    'user_id' => $user->id,
                    'bank_account_id' => $account?->id,
                    'category_id' => $category->id,
                    'type' => 'expense',
                    'amount' => $expenseAmount,
                    'currency_id' => $rsd->id,
                    'exchange_rate' => 1,
                    'base_amount' => $expenseAmount,
                    'date' => $monthStart->addDays(fake()->numberBetween(0, $monthStart->daysInMonth - 1)),
                    'description' => fake()->randomElement($descriptions),
                    'payment_method' => $useBank ? 'bank_account' : 'cash',
                    'notes' => fake()->boolean(20) ? fake()->sentence() : null,
                ]);
            }
        }
    }
}
