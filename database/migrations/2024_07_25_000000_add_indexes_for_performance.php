<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForPerformance extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to system_configurations table with error handling
        try {
            Schema::table('system_configurations', function (Blueprint $table) {
                $table->index(['category', 'key'], 'system_configurations_category_key_index');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        try {
            Schema::table('system_configurations', function (Blueprint $table) {
                $table->index(['category', 'is_active'], 'system_configurations_category_active_index');
            });
        } catch (\Exception $e) {
            // Index might already exist, continue
        }

        // Add indexes to projects table
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->index(['status'], 'projects_status_index');
                $table->index(['developer_id'], 'projects_developer_id_index');
                $table->index(['type'], 'projects_type_index');
                $table->index(['created_at'], 'projects_created_at_index');
                $table->index(['updated_at'], 'projects_updated_at_index');
            });
        } catch (\Exception $e) {
            // Indexes might already exist, continue
        }

        // Add indexes to project_transactions table
        try {
            Schema::table('project_transactions', function (Blueprint $table) {
                $table->index(['project_id'], 'project_transactions_project_id_index');
                $table->index(['type'], 'project_transactions_type_index');
                $table->index(['status'], 'project_transactions_status_index');
                $table->index(['created_at'], 'project_transactions_created_at_index');
                $table->index(['transaction_date'], 'project_transactions_date_index');
            });
        } catch (\Exception $e) {
            // Indexes might already exist, continue
        }

        // Add composite indexes for common queries
        try {
            Schema::table('project_transactions', function (Blueprint $table) {
                $table->index(['project_id', 'type'], 'project_transactions_project_type_index');
                $table->index(['type', 'status'], 'project_transactions_type_status_index');
                $table->index(['type', 'created_at'], 'project_transactions_type_date_index');
            });
        } catch (\Exception $e) {
            // Indexes might already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from system_configurations table
        Schema::table('system_configurations', function (Blueprint $table) {
            $table->dropIndex('system_configurations_category_key_index');
            $table->dropIndex('system_configurations_category_active_index');
        });

        // Remove indexes from projects table
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropIndex('projects_status_index');
                $table->dropIndex('projects_developer_id_index');
                $table->dropIndex('projects_type_index');
                $table->dropIndex('projects_created_at_index');
                $table->dropIndex('projects_updated_at_index');
            });
        } catch (\Exception $e) {
            // Indexes might not exist, continue
        }

        // Remove indexes from project_transactions table
        Schema::table('project_transactions', function (Blueprint $table) {
            $table->dropIndex('project_transactions_project_id_index');
            $table->dropIndex('project_transactions_type_index');
            $table->dropIndex('project_transactions_status_index');
            $table->dropIndex('project_transactions_created_at_index');
            $table->dropIndex('project_transactions_date_index');
            $table->dropIndex('project_transactions_project_type_index');
            $table->dropIndex('project_transactions_type_status_index');
            $table->dropIndex('project_transactions_type_date_index');
        });
    }
}
