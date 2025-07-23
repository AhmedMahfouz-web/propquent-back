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
        if (!Schema::hasTable('report_cache')) {
            Schema::create('report_cache', function (Blueprint $table) {
                $table->id();
                $table->string('report_type', 100);
                $table->string('report_key', 255);
                $table->json('report_data');
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['report_type', 'report_key']);
                $table->index('expires_at');
            });
        } else {
            // Ensure the report_cache table has the necessary columns
            Schema::table('report_cache', function (Blueprint $table) {
                if (!Schema::hasColumn('report_cache', 'report_type')) {
                    $table->string('report_type', 100)->after('id');
                }

                if (!Schema::hasColumn('report_cache', 'report_key')) {
                    $table->string('report_key', 255)->after('report_type');
                }

                if (!Schema::hasColumn('report_cache', 'report_data')) {
                    $table->json('report_data')->after('report_key');
                }

                if (!Schema::hasColumn('report_cache', 'expires_at')) {
                    $table->timestamp('expires_at')->after('report_data');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to drop the table if it already existed
        // So we do nothing here
    }
};
