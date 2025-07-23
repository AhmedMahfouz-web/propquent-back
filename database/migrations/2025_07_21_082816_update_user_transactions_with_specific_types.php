<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if transaction_type column already exists
        if (!Schema::hasColumn('user_transactions', 'transaction_type')) {
            Schema::table('user_transactions', function (Blueprint $table) {
                $table->enum('transaction_type', ['deposit', 'withdraw'])->after('user_id')->nullable();
            });
        }

        // Update existing data if type column still exists
        if (Schema::hasColumn('user_transactions', 'type')) {
            DB::statement("UPDATE user_transactions SET transaction_type = 'deposit' WHERE type IN ('deposit', 'investment')");
            DB::statement("UPDATE user_transactions SET transaction_type = 'withdraw' WHERE type NOT IN ('deposit', 'investment')");

            // Drop the old type column
            Schema::table('user_transactions', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        // Ensure transaction_type is not nullable and update status column
        Schema::table('user_transactions', function (Blueprint $table) {
            // Make transaction_type not nullable if it exists
            if (Schema::hasColumn('user_transactions', 'transaction_type')) {
                $table->enum('transaction_type', ['deposit', 'withdraw'])->nullable(false)->change();
            }

            // Update the status column to have specific values
            if (Schema::hasColumn('user_transactions', 'status')) {
                $table->dropColumn('status');
            }
            $table->enum('status', ['done', 'pending', 'cancelled'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
            $table->string('transaction_type')->after('user_id');

            $table->dropColumn('status');
            $table->string('status')->default('pending');
        });
    }
};
