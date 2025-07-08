<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('type', ['Deposit', 'Withdraw']);
            $table->decimal('amount', 15, 2);
            $table->timestamp('transaction_date')->useCurrent();
            $table->timestamp('actual_date')->nullable();
            $table->enum('method', ['Cheque', 'Bank Transfer', 'Cash']);
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['Done', 'Pending', 'Canceled']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_transactions');
    }
};
