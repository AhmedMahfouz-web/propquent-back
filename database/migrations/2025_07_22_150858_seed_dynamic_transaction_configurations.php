<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemConfiguration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Seed transaction methods
        SystemConfiguration::where('category', 'transaction_methods')->delete();

        $transactionMethods = [
            [
                'category' => 'transaction_methods',
                'key' => 'bank_transfer',
                'value' => 'bank_transfer',
                'label' => 'Bank Transfer',
                'description' => 'Payment via bank transfer',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'transaction_methods',
                'key' => 'cash',
                'value' => 'cash',
                'label' => 'Cash',
                'description' => 'Cash payment',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category' => 'transaction_methods',
                'key' => 'check',
                'value' => 'check',
                'label' => 'Check',
                'description' => 'Payment by check',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category' => 'transaction_methods',
                'key' => 'credit_card',
                'value' => 'credit_card',
                'label' => 'Credit Card',
                'description' => 'Credit card payment',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'category' => 'transaction_methods',
                'key' => 'online_payment',
                'value' => 'online_payment',
                'label' => 'Online Payment',
                'description' => 'Online payment gateway',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($transactionMethods as $method) {
            SystemConfiguration::create($method);
        }

        // Seed property types
        SystemConfiguration::where('category', 'property_types')->delete();

        $propertyTypes = [
            [
                'category' => 'property_types',
                'key' => 'apartment',
                'value' => 'apartment',
                'label' => 'Apartment',
                'description' => 'Residential apartment unit',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'property_types',
                'key' => 'villa',
                'value' => 'villa',
                'label' => 'Villa',
                'description' => 'Standalone villa property',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category' => 'property_types',
                'key' => 'townhouse',
                'value' => 'townhouse',
                'label' => 'Townhouse',
                'description' => 'Townhouse property',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category' => 'property_types',
                'key' => 'penthouse',
                'value' => 'penthouse',
                'label' => 'Penthouse',
                'description' => 'Luxury penthouse unit',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'category' => 'property_types',
                'key' => 'studio',
                'value' => 'studio',
                'label' => 'Studio',
                'description' => 'Studio apartment',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'category' => 'property_types',
                'key' => 'duplex',
                'value' => 'duplex',
                'label' => 'Duplex',
                'description' => 'Duplex property',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($propertyTypes as $type) {
            SystemConfiguration::create($type);
        }

        // Seed investment types
        SystemConfiguration::where('category', 'investment_types')->delete();

        $investmentTypes = [
            [
                'category' => 'investment_types',
                'key' => 'buy_to_hold',
                'value' => 'buy_to_hold',
                'label' => 'Buy to Hold',
                'description' => 'Long-term investment strategy',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'investment_types',
                'key' => 'fix_and_flip',
                'value' => 'fix_and_flip',
                'label' => 'Fix and Flip',
                'description' => 'Renovation and quick sale strategy',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category' => 'investment_types',
                'key' => 'rental_income',
                'value' => 'rental_income',
                'label' => 'Rental Income',
                'description' => 'Focus on rental income generation',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category' => 'investment_types',
                'key' => 'commercial',
                'value' => 'commercial',
                'label' => 'Commercial',
                'description' => 'Commercial property investment',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($investmentTypes as $type) {
            SystemConfiguration::create($type);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the seeded configurations
        SystemConfiguration::whereIn('category', [
            'transaction_methods',
            'property_types',
            'investment_types'
        ])->delete();
    }
};
