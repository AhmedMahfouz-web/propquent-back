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
        // Rename the table
        Schema::rename('project_evaluations', 'value_corrections');
        
        // Rename the columns
        Schema::table('value_corrections', function (Blueprint $table) {
            $table->renameColumn('evaluation_date', 'correction_date');
            $table->renameColumn('evaluation_amount', 'correction_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename columns back
        Schema::table('value_corrections', function (Blueprint $table) {
            $table->renameColumn('correction_date', 'evaluation_date');
            $table->renameColumn('correction_amount', 'evaluation_amount');
        });
        
        // Rename table back
        Schema::rename('value_corrections', 'project_evaluations');
    }
};
