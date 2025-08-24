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
        Schema::table('users', function (Blueprint $table) {
            // Add indexes for search performance
            $table->index('full_name', 'users_full_name_index');
            $table->index('custom_id', 'users_custom_id_index');
            
            // Composite index for common queries
            $table->index(['full_name', 'custom_id'], 'users_search_composite_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_full_name_index');
            $table->dropIndex('users_custom_id_index');
            $table->dropIndex('users_search_composite_index');
        });
    }
};
