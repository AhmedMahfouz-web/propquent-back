<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemConfiguration;

class SeedWhatOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:what-options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the "what" options for project transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $whatOptions = [
            ['key' => 'unit_installment', 'label' => 'Unit Installment'],
            ['key' => 'over_price', 'label' => 'Over Price'],
            ['key' => 'club_owner', 'label' => 'Club-Owner'],
            ['key' => 'club_unit', 'label' => 'Club-Unit'],
            ['key' => 'commission', 'label' => 'Commission'],
            ['key' => 'advertising', 'label' => 'Advertising'],
            ['key' => 'propquant', 'label' => 'PropQuant'],
            ['key' => 'maintenance', 'label' => 'Maintenance'],
            ['key' => 'rent', 'label' => 'Rent'],
            ['key' => 'other', 'label' => 'Other'],
        ];

        $this->info('Seeding "what" options for project transactions...');

        foreach ($whatOptions as $option) {
            SystemConfiguration::updateOrCreate(
                [
                    'category' => 'transaction_what',
                    'key' => $option['key'],
                ],
                [
                    'value' => $option['label'],
                    'label' => $option['label'],
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
            
            $this->line("✓ Added: {$option['label']}");
        }

        $this->info('Successfully seeded ' . count($whatOptions) . ' "what" options!');
        
        return Command::SUCCESS;
    }
}
