<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the enum definitions to include the new values

        // Update projects status enum to include new values
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('available','sold','reserved','cancelled','on-going','exited') NOT NULL DEFAULT 'available'");

        // Update projects stage enum to include new values
        DB::statement("ALTER TABLE projects MODIFY COLUMN stage ENUM('planning','construction','completed','delivered','holding','buying','sold','cancelled') NOT NULL DEFAULT 'planning'");

        // Now update the data with the new enum values

        // Update Project status values
        DB::statement("
            UPDATE projects
            SET status = CASE
                WHEN status = 'completed' THEN 'exited'
                WHEN status = 'delivered' THEN 'exited'
                WHEN status = 'available' THEN 'on-going'
                WHEN status = 'sold' THEN 'exited'
                WHEN status = 'reserved' THEN 'on-going'
                WHEN status = 'cancelled' THEN 'on-going'
                ELSE 'on-going'
            END
        ");

        // Update Project stage values
        DB::statement("
            UPDATE projects
            SET stage = CASE
                WHEN stage = 'planning' THEN 'holding'
                WHEN stage = 'construction' THEN 'buying'
                WHEN stage = 'completed' THEN 'sold'
                WHEN stage = 'delivered' THEN 'sold'
                WHEN stage = 'cancelled' THEN 'cancelled'
                ELSE 'holding'
            END
        ");

        // Update Project target values
        DB::statement("
            UPDATE projects
            SET target_1 = CASE
                WHEN target_1 IS NULL OR target_1 = '' THEN 'asset appreciation'
                ELSE 'asset appreciation'
            END,
            target_2 = CASE
                WHEN target_2 IS NULL OR target_2 = '' THEN 'rent'
                ELSE 'rent'
            END
        ");

        // Ensure exit_date is newer than entry_date
        DB::statement("
            UPDATE projects
            SET exit_date = DATE_ADD(entry_date, INTERVAL 1 DAY)
            WHERE exit_date IS NOT NULL AND entry_date IS NOT NULL AND exit_date <= entry_date
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse these changes as they are data normalization
    }
};
