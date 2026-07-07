<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('iso_code', 3)->unique();
            $table->string('name', 100);
            $table->string('symbol', 10);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::table('currencies')->insert([
            ['iso_code' => 'RSD', 'name' => 'Serbian Dinar', 'symbol' => 'RSD', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['iso_code' => 'EUR', 'name' => 'Euro', 'symbol' => 'EUR', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['iso_code' => 'USD', 'name' => 'US Dollar', 'symbol' => 'USD', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['iso_code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['iso_code' => 'GBP', 'name' => 'British Pound', 'symbol' => 'GBP', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['iso_code' => 'BAM', 'name' => 'Bosnia and Herzegovina Convertible Mark', 'symbol' => 'BAM', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
