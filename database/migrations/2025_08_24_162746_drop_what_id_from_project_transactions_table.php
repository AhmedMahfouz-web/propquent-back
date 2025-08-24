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
        Schema::table('project_transactions', function (Blueprint $table) {
            $table->dropForeign(['what_id']);
            $table->dropColumn('what_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_transactions', function (Blueprint $table) {
            $table->foreignId('what_id')->nullable()->constrained('transaction_whats')->onDelete('cascade');
        });
    }
};
