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
        Schema::table('project_transactions', function (Blueprint $table) {
            // Add transaction_category field if not exists
            if (!Schema::hasColumn('project_transactions', 'transaction_category')) {
                $table->string('transaction_category')->after('amount')->nullable();
            }

            // Add financial_type field for revenue/expense categorization
            $table->enum('financial_type', ['revenue', 'expense'])->after('type')->nullable();

            // Add indexes for efficient querying
            $table->index('financial_type');
            $table->index('transaction_category');
            $table->index('transaction_date');
        });

        // Migrateexisting data based on transaction_what categories
        DB::statement("
            UPDATE project_transactions pt
            JOIN transaction_whats tw ON pt.what_id = tw.id
            SET pt.transaction_category = tw.category,
                pt.financial_type = CASE
                    WHEN pt.type IN ('income', 'revenue', 'sale', 'rental') THEN 'revenue'
                    ELSE 'expense'
                END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('project_transactions', 'transaction_category')) {
                $table->dropColumn('transaction_category');
            }
            $table->dropColumn('financial_type');
            $table->dropIndex(['financial_type']);
            $table->dropIndex(['transaction_category']);
            $table->dropIndex(['transaction_date']);
        });
    }
};
