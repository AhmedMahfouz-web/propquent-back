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
        Schema::table('transaction_whats', function (Blueprint $table) {
            // Add financial_type field for revenue/expense categorization
            $table->enum('financial_type', ['revenue', 'expense'])->after('category')->nullable();

            // Ensure category field is not null and has a default
            $table->string('category')->nullable(false)->default('other')->change();

            // Add index for efficient querying
            $table->index('category');
            $table->index('financial_type');
        });

        // Set default financial_type based on category
        DB::statement("
            UPDATE transaction_whats
            SET financial_type = CASE
                WHEN category IN ('sales', 'rental', 'investment', 'income') THEN 'revenue'
                ELSE 'expense'
            END
        ");

        // Ensure all categories are properly set
        DB::statement("
            UPDATE transaction_whats
            SET category = 'other'
            WHERE category IS NULL OR category = ''
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_whats', function (Blueprint $table) {
            $table->dropColumn('financial_type');
            $table->string('category')->nullable()->change();
            $table->dropIndex(['category']);
            $table->dropIndex(['financial_type']);
        });
    }
};
