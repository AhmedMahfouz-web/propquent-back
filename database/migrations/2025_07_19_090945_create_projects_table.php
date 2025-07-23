<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('title');
            $table->foreignId('developer_id')->constrained()->onDelete('cascade');
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->string('unit_no')->nullable();
            $table->string('project')->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->string('garden_area')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->string('floor')->nullable();
            $table->enum('status', ['available', 'sold', 'reserved', 'cancelled'])->default('available');
            $table->enum('stage', ['planning', 'construction', 'completed', 'delivered'])->default('planning');
            $table->string('target_1')->nullable();
            $table->string('target_2')->nullable();
            $table->date('entry_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('investment_type')->nullable();
            $table->string('document')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
