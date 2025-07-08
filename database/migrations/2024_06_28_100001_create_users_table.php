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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password_hash')->nullable(); // Nullable if using social login
            $table->string('auth_provider')->default('email'); // 'google', 'apple', or 'email'
            $table->string('provider_user_id')->nullable(); // ID from Google or Apple
            $table->boolean('email_verified')->default(false);
            $table->string('phone_number')->nullable();
            $table->string('country')->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
