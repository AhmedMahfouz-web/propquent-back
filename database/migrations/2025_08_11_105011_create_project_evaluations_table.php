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
        Schema::create('project_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('project_key');
            $table->date('evaluation_date'); // First day of the month
            $table->decimal('evaluation_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('project_key')->references('key')->on('projects')->onDelete('cascade');
            $table->unique(['project_key', 'evaluation_date']);
            $table->index(['project_key', 'evaluation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_evaluations');
    }
};
