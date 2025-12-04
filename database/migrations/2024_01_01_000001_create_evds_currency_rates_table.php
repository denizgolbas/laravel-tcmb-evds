<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evds_currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->index();
            $table->enum('type', ['buy', 'sell'])->default('sell')->index(); // Buy (Alış) veya Sell (Satış)
            $table->enum('market_type', ['forex', 'banknote'])->default('forex')->index(); // Forex (Döviz) veya Banknote (Efektif)
            $table->decimal('rate', 12, 6); // Supports up to 6 decimal places (e.g., 45.676890)
            $table->date('date')->index();
            $table->timestamps();

            $table->unique(['code', 'type', 'market_type', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evds_currency_rates');
    }
};

