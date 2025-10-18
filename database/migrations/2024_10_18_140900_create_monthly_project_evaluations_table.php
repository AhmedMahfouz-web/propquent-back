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
        Schema::create('monthly_project_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('project_key');
            $table->date('month_date'); // First day of the month (Y-m-01)
            $table->decimal('asset_evaluation', 15, 2)->default(0);
            $table->decimal('expense_asset', 15, 2)->default(0);
            $table->decimal('revenue_asset', 15, 2)->default(0);
            $table->decimal('value_correction', 15, 2)->default(0);
            $table->decimal('previous_evaluation', 15, 2)->default(0);
            $table->boolean('is_after_exit')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->unique(['project_key', 'month_date']);
            $table->index('month_date');
            $table->index('project_key');
            
            // Foreign key
            $table->foreign('project_key')->references('key')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_project_evaluations');
    }
};
