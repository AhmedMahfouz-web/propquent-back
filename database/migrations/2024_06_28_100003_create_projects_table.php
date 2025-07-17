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
            $table->text('description')->nullable();
            $table->foreignId('developer_id')->constrained('developers');
            $table->string('location');
            $table->string('type');
            $table->string('unit_no');
            $table->string('project');
            $table->integer('area');
            $table->string('garden_area')->nullable();
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->string('floor');
            $table->enum('status', ['pending', 'approved', 'rejected', 'On-Going', 'Exited']);
            $table->string('stage');
            $table->string('target_1')->nullable();
            $table->string('target_2')->nullable();
            $table->date('entry_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('investment_type');
            $table->string('image_url')->nullable();
            $table->string('document')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
