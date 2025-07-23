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
            // Drop the existing type column if it exists
            if (Schema::hasColumn('project_transactions', 'type')) {
                $table->dropColumn('type');
            }

            // Add the new financial_type column with specific enum values if it doesn't exist
            if (!Schema::hasColumn('project_transactions', 'financial_type')) {
                $table->enum('financial_type', ['revenue', 'expense'])->after('project_key');
            } else {
                // If it exists, modify it to have the specific enum values
                $table->dropColumn('financial_type');
                $table->enum('financial_type', ['revenue', 'expense'])->after('project_key');
            }

            // Update the serving column to have specific values
            if (Schema::hasColumn('project_transactions', 'serving')) {
                $table->dropColumn('serving');
            }
            $table->enum('serving', ['asset', 'operation'])->nullable()->after('financial_type');

            // Update the status column to have specific values
            $table->dropColumn('status');
            $table->enum('status', ['done', 'pending', 'cancelled'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_transactions', function (Blueprint $table) {
            $table->dropColumn('financial_type');
            $table->string('type')->after('project_key');

            $table->dropColumn('serving');
            $table->string('serving')->nullable()->after('type');

            $table->dropColumn('status');
            $table->string('status')->default('pending');
        });
    }
};
