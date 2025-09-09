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
        // Add indexes for project_transactions table to optimize cashflow queries
        Schema::table('project_transactions', function (Blueprint $table) {
            // Composite index for cashflow calculations
            $table->index(['project_key', 'status', 'financial_type'], 'idx_project_status_type');
            
            // Index for date-based queries
            $table->index(['transaction_date', 'status'], 'idx_transaction_date_status');
            
            // Index for pending installments
            $table->index(['status', 'transaction_date'], 'idx_status_date');
            
            // Index for monthly aggregations
            $table->index(['financial_type', 'status', 'transaction_date'], 'idx_type_status_date');
        });

        // Add indexes for user_transactions table
        Schema::table('user_transactions', function (Blueprint $table) {
            // Composite index for cashflow calculations
            $table->index(['transaction_type', 'status'], 'idx_user_type_status');
            
            // Index for date-based queries
            $table->index(['transaction_date', 'status'], 'idx_user_date_status');
            
            // Index for monthly aggregations
            $table->index(['transaction_type', 'status', 'transaction_date'], 'idx_user_type_status_date');
        });

        // Add indexes for projects table
        Schema::table('projects', function (Blueprint $table) {
            // Index for status filtering
            $table->index(['status'], 'idx_project_status');
            
            // Index for developer filtering
            $table->index(['developer_id'], 'idx_project_developer');
            
            // Composite index for common filters
            $table->index(['status', 'developer_id'], 'idx_project_status_developer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_project_status_type');
            $table->dropIndex('idx_transaction_date_status');
            $table->dropIndex('idx_status_date');
            $table->dropIndex('idx_type_status_date');
        });

        Schema::table('user_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_user_type_status');
            $table->dropIndex('idx_user_date_status');
            $table->dropIndex('idx_user_type_status_date');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('idx_project_status');
            $table->dropIndex('idx_project_developer');
            $table->dropIndex('idx_project_status_developer');
        });
    }
};
