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
        Schema::table('monthly_project_evaluations', function (Blueprint $table) {
            // Add operation fields
            $table->decimal('expense_operation', 15, 2)->default(0)->after('value_correction');
            $table->decimal('revenue_operation', 15, 2)->default(0)->after('expense_operation');
            $table->decimal('profit_operation', 15, 2)->default(0)->after('revenue_operation');
            
            // Add cumulative profit fields
            $table->decimal('profit_asset_cumulative', 15, 2)->default(0)->after('profit_operation');
            $table->decimal('profit_operation_cumulative', 15, 2)->default(0)->after('profit_asset_cumulative');
            $table->decimal('total_profit_cumulative', 15, 2)->default(0)->after('profit_operation_cumulative');
            
            // Add totals for convenience
            $table->decimal('expense_total', 15, 2)->default(0)->after('total_profit_cumulative');
            $table->decimal('revenue_total', 15, 2)->default(0)->after('expense_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_project_evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'expense_operation',
                'revenue_operation', 
                'profit_operation',
                'profit_asset_cumulative',
                'profit_operation_cumulative',
                'total_profit_cumulative',
                'expense_total',
                'revenue_total'
            ]);
        });
    }
};
