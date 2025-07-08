<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_what', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Added a name column as the original schema was empty
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_what');
    }
};
