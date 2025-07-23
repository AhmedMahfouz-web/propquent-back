<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionWhatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $whats = [
            ['id' => 1, 'name' => 'Initial Investment', 'category' => 'investment', 'financial_type' => 'expense'],
            ['id' => 2, 'name' => 'Land Purchase', 'category' => 'acquisition', 'financial_type' => 'expense'],
            ['id' => 3, 'name' => 'Construction Costs', 'category' => 'development', 'financial_type' => 'expense'],
            ['id' => 4, 'name' => 'Permit Fees', 'category' => 'legal', 'financial_type' => 'expense'],
            ['id' => 5, 'name' => 'Architectural Design', 'category' => 'design', 'financial_type' => 'expense'],
            ['id' => 6, 'name' => 'Rental Income', 'category' => 'income', 'financial_type' => 'revenue'],
            ['id' => 7, 'name' => 'Property Sale', 'category' => 'sale', 'financial_type' => 'revenue'],
            ['id' => 8, 'name' => 'Maintenance', 'category' => 'operations', 'financial_type' => 'expense'],
            ['id' => 9, 'name' => 'Utilities', 'category' => 'operations', 'financial_type' => 'expense'],
            ['id' => 10, 'name' => 'Property Taxes', 'category' => 'legal', 'financial_type' => 'expense'],
        ];

        foreach ($whats as $what) {
            DB::table('transaction_whats')->insert([
                'id' => $what['id'],
                'name' => $what['name'],
                'description' => $what['name'],
                'category' => $what['category'],
                'financial_type' => $what['financial_type'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
