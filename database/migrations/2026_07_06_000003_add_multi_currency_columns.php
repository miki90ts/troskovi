<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('default_currency_id')
                ->nullable()
                ->after('password')
                ->constrained('currencies')
                ->nullOnDelete();
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->foreignId('currency_id')
                ->nullable()
                ->after('currency')
                ->constrained('currencies')
                ->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('currency_id')
                ->nullable()
                ->after('amount')
                ->constrained('currencies')
                ->nullOnDelete();
            $table->decimal('exchange_rate', 16, 6)->nullable()->after('currency_id');
            $table->decimal('base_amount', 16, 2)->nullable()->after('exchange_rate');
            $table->index(['user_id', 'currency_id', 'date']);
        });

        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->foreignId('currency_id')
                ->nullable()
                ->after('amount')
                ->constrained('currencies')
                ->nullOnDelete();
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->foreignId('currency_id')
                ->nullable()
                ->after('amount')
                ->constrained('currencies')
                ->nullOnDelete();
            $table->decimal('exchange_rate', 16, 6)->nullable()->after('currency_id');
            $table->decimal('base_amount', 16, 2)->nullable()->after('exchange_rate');
            $table->decimal('remaining_base_amount', 16, 2)->nullable()->after('remaining_amount');
        });

        Schema::table('spending_targets', function (Blueprint $table) {
            $table->foreignId('currency_id')
                ->nullable()
                ->after('target_amount')
                ->constrained('currencies')
                ->nullOnDelete();
        });

        Schema::table('account_transfers', function (Blueprint $table) {
            $table->foreignId('from_currency_id')
                ->nullable()
                ->after('amount')
                ->constrained('currencies')
                ->nullOnDelete();
            $table->foreignId('to_currency_id')
                ->nullable()
                ->after('from_currency_id')
                ->constrained('currencies')
                ->nullOnDelete();
            $table->decimal('to_amount', 12, 2)->nullable()->after('to_currency_id');
            $table->decimal('exchange_rate', 16, 6)->nullable()->after('to_amount');
            $table->decimal('base_amount', 16, 2)->nullable()->after('exchange_rate');
        });

        $rsdId = DB::table('currencies')->where('iso_code', 'RSD')->value('id');

        if (! $rsdId) {
            throw new RuntimeException('Base currency RSD must exist before multi-currency backfill.');
        }

        DB::table('users')
            ->whereNull('default_currency_id')
            ->update(['default_currency_id' => $rsdId]);

        DB::table('bank_accounts')
            ->whereNull('currency_id')
            ->update([
                'currency' => 'RSD',
                'currency_id' => $rsdId,
            ]);

        DB::table('transactions')
            ->whereNull('currency_id')
            ->update([
                'currency_id' => $rsdId,
                'exchange_rate' => 1,
                'base_amount' => DB::raw('amount'),
            ]);

        DB::table('recurring_transactions')
            ->whereNull('currency_id')
            ->update(['currency_id' => $rsdId]);

        DB::table('debts')
            ->whereNull('currency_id')
            ->update([
                'currency_id' => $rsdId,
                'exchange_rate' => 1,
                'base_amount' => DB::raw('amount'),
                'remaining_base_amount' => DB::raw('remaining_amount'),
            ]);

        DB::table('spending_targets')
            ->whereNull('currency_id')
            ->update(['currency_id' => $rsdId]);

        DB::table('account_transfers')
            ->whereNull('from_currency_id')
            ->update([
                'from_currency_id' => $rsdId,
                'to_currency_id' => $rsdId,
                'to_amount' => DB::raw('amount'),
                'exchange_rate' => 1,
                'base_amount' => DB::raw('amount'),
            ]);
    }

    public function down(): void
    {
        Schema::table('account_transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('from_currency_id');
            $table->dropConstrainedForeignId('to_currency_id');
            $table->dropColumn(['to_amount', 'exchange_rate', 'base_amount']);
        });

        Schema::table('spending_targets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('currency_id');
        });

        Schema::table('debts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('currency_id');
            $table->dropColumn(['exchange_rate', 'base_amount', 'remaining_base_amount']);
        });

        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('currency_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'currency_id', 'date']);
            $table->dropConstrainedForeignId('currency_id');
            $table->dropColumn(['exchange_rate', 'base_amount']);
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('currency_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_currency_id');
        });
    }
};
