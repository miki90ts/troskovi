<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spending_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('period');
            $table->decimal('target_amount', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'period', 'is_active']);
            $table->unique(['user_id', 'period', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spending_targets');
    }
};
