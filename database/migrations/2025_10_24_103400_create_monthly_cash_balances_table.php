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
        Schema::create('monthly_cash_balances', function (Blueprint $table) {
            $table->id();
            $table->date('month_date')->unique(); // e.g., '2024-11-01'
            $table->decimal('revenue', 20, 2)->default(0);
            $table->decimal('expense', 20, 2)->default(0);
            $table->decimal('deposits', 20, 2)->default(0);
            $table->decimal('withdrawals', 20, 2)->default(0);
            $table->decimal('cash_balance', 20, 2)->default(0); // Cumulative cash
            $table->decimal('previous_month_cash', 20, 2)->default(0);
            $table->timestamps();
            
            // Indexes for fast lookups
            $table->index('month_date');
            $table->index(['month_date', 'cash_balance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_cash_balances');
    }
};
