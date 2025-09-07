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
        Schema::table('value_corrections', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['project_key']);
            
            // Add the foreign key constraint with ON UPDATE CASCADE
            $table->foreign('project_key')
                  ->references('key')
                  ->on('projects')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('value_corrections', function (Blueprint $table) {
            // Drop the updated foreign key constraint
            $table->dropForeign(['project_key']);
            
            // Restore the original foreign key constraint (without ON UPDATE CASCADE)
            $table->foreign('project_key')
                  ->references('key')
                  ->on('projects')
                  ->onDelete('cascade');
        });
    }
};
