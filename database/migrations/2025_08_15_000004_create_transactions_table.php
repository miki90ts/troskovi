<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recurring_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // income, expense
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('description');
            $table->text('notes')->nullable();
            $table->string('payment_method')->default('cash');
            $table->string('receipt_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type', 'date']);
            $table->index('bank_account_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
