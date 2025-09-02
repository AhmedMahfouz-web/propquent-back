<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('project_key');
            $table->string('financial_type');
            $table->string('serving')->nullable();
            $table->foreignId('what_id')->constrained('transaction_whats')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->date('transaction_date');
            $table->string('method')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('project_key')->references('key')->on('projects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_transactions');
    }
};
