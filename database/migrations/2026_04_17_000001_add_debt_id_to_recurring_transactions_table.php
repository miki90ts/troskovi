<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->foreignId('debt_id')
                ->nullable()
                ->after('category_id')
                ->constrained('debts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('debt_id');
        });
    }
};
