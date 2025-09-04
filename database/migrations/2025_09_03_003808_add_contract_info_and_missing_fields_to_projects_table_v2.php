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
        Schema::table('projects', function (Blueprint $table) {
            // Contract Info fields
            $table->date('reservation_date')->nullable();
            $table->date('contract_date')->nullable();
            $table->integer('years_of_installment')->nullable();
            $table->decimal('total_contract_value', 15, 2)->nullable();

            // Missing fields (excluding garden_area as it already exists)
            $table->text('map_location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'reservation_date',
                'contract_date',
                'years_of_installment',
                'total_contract_value',
                'map_location'
            ]);
        });
    }
};
