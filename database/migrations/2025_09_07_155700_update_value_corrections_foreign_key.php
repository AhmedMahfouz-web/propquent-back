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
            // When the table was renamed from 'project_evaluations', the foreign key name was not updated.
            // We must drop it using its original name.
            $table->dropForeign('project_evaluations_project_key_foreign');

            // Add the new foreign key constraint with ON UPDATE CASCADE
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
            // Drop the new foreign key constraint (Laravel will guess the name correctly now)
            $table->dropForeign(['project_key']);

            // Re-add the original foreign key constraint without ON UPDATE CASCADE
            $table->foreign('project_key')
                  ->references('key')
                  ->on('projects')
                  ->onDelete('cascade');
            $table->dropForeign(['project_key']);
            
            // Restore the original foreign key constraint (without ON UPDATE CASCADE)
            $table->foreign('project_key')
                  ->references('key')
                  ->on('projects')
                  ->onDelete('cascade');
        });
    }
};
