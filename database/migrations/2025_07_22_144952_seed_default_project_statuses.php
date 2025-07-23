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
        // Remove old project status configurations if they exist
        SystemConfiguration::where('category', 'project_statuses')->delete();

        // Seed the new project statuses (exited, on-going)
        $projectStatuses = [
            [
                'category' => 'project_statuses',
                'key' => 'on-going',
                'value' => 'on-going',
                'label' => 'On-going',
                'description' => 'Project is currently active and ongoing',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'project_statuses',
                'key' => 'exited',
                'value' => 'exited',
                'label' => 'Exited',
                'description' => 'Project has been completed and exited',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($projectStatuses as $status) {
            SystemConfiguration::create($status);
        }

        // Also seed project stages based on the constants in the Project model
        SystemConfiguration::where('category', 'project_stages')->delete();

        $projectStages = [
            [
                'category' => 'project_stages',
                'key' => 'holding',
                'value' => 'holding',
                'label' => 'Holding',
                'description' => 'Project is being held',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'project_stages',
                'key' => 'buying',
                'value' => 'buying',
                'label' => 'Buying',
                'description' => 'Project is in buying phase',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category' => 'project_stages',
                'key' => 'selling',
                'value' => 'selling',
                'label' => 'Selling',
                'description' => 'Project is being sold',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category' => 'project_stages',
                'key' => 'cancelled',
                'value' => 'cancelled',
                'label' => 'Cancelled',
                'description' => 'Project has been cancelled',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'category' => 'project_stages',
                'key' => 'sold',
                'value' => 'sold',
                'label' => 'Sold',
                'description' => 'Project has been sold',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'category' => 'project_stages',
                'key' => 'rented',
                'value' => 'rented',
                'label' => 'Rented',
                'description' => 'Project is being rented',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'category' => 'project_stages',
                'key' => 'renovation',
                'value' => 'renovation',
                'label' => 'Renovation',
                'description' => 'Project is under renovation',
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($projectStages as $stage) {
            SystemConfiguration::create($stage);
        }

        // Seed project targets
        SystemConfiguration::where('category', 'project_targets')->delete();

        $projectTargets = [
            [
                'category' => 'project_targets',
                'key' => 'asset_appreciation',
                'value' => 'asset appreciation',
                'label' => 'Asset Appreciation',
                'description' => 'Investment focused on asset value appreciation',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category' => 'project_targets',
                'key' => 'rent',
                'value' => 'rent',
                'label' => 'Rent',
                'description' => 'Investment focused on rental income',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($projectTargets as $target) {
            SystemConfiguration::create($target);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the seeded configurations
        SystemConfiguration::whereIn('category', [
            'project_statuses',
            'project_stages',
            'project_targets'
        ])->delete();
    }
};
