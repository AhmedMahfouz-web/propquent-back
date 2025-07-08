<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_transaction', function (Blueprint $table) {
            $table->id();
            $table->string('project_key');
            $table->foreign('project_key')->references('key')->on('projects');
            $table->enum('type', ['Expense', 'Revenue']);
            $table->enum('serving', ['Asset', 'Operation']);
            $table->foreignId('what_id')->constrained('transaction_what');
            $table->decimal('amount', 15, 2);
            $table->timestamp('due_date')->nullable();
            $table->timestamp('actual_date')->nullable();
            $table->timestamp('transaction_date')->useCurrent();
            $table->enum('method', ['Cheque', 'Bank Transfer', 'Cash']);
            $table->string('reference_no')->nullable();
            $table->enum('status', ['Done', 'Pending', 'Canceled']);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_transaction');
    }
};
