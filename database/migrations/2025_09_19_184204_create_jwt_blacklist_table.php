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
        Schema::create('jwt_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('jti')->unique(); // JWT ID
            $table->timestamp('expires_at');
            $table->timestamps();
            
            // Index for performance
            $table->index(['jti', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jwt_blacklist');
    }
};
