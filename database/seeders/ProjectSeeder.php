<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Developer;
use App\Models\SystemConfiguration;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Total number of projects to create
     */
    private const TOTAL_PROJECTS = 75;

    /**
     * Distribution of projects across months (ensures good spread with seasonal patterns)
     * Higher activity in spring/fall, lower in summer/winter
     */
    private const MONTHLY_DISTRIBUTION = [
        1 => 5,   // January (post-holiday slowdown)
        2 => 6,   // February
        3 => 8,   // March (spring activity)
        4 => 7,   // April
        5 => 8,   // May (peak spring)
        6 => 6,   // June (summer start)
        7 => 5,   // July (summer slowdown)
        8 => 5,   // August (vacation period)
        9 => 7,   // September (back to business)
        10 => 8,  // October (fall activity)
        11 => 6,  // November
        12 => 4,  // December (holiday slowdown)
    ];

    /**
     * Status distribution (65% ongoing, 35% exited for realistic business scenario)
     * Slightly more exits to show better trend analysis
     */
    private const STATUS_DISTRIBUTION = [
        'on-going' => 0.65,
        'exited' => 0.35,
    ];

    public function run(): void
    {
        $faker = Faker::create();
        $developerIds = Developer::pluck('id')->toArray();

        if (empty($developerIds)) {
            $this->command->info('No developers found. Skipping project seeding.');
            return;
        }

        // Get dynamic configuration options
        $propertyTypes = $this->getConfigurationOptions('property_types');
        $investmentTypes = $this->getConfigurationOptions('investment_types');
        $projectStages = $this->getConfigurationOptions('project_stages');

        $this->command->info('Creating projects with realistic dates and distribution...');

        // Create projects distributed across the last 12 months
        $projectsCreated = 0;
        $statusCounts = ['on-going' => 0, 'exited' => 0];

        // Calculate target counts for each status
        $targetOngoing = (int) (self::TOTAL_PROJECTS * self::STATUS_DISTRIBUTION['on-going']);
        $targetExited = self::TOTAL_PROJECTS - $targetOngoing;

        for ($monthOffset = 11; $monthOffset >= 0; $monthOffset--) {
            $targetMonth = Carbon::now()->subMonths($monthOffset);
            $projectsForMonth = self::MONTHLY_DISTRIBUTION[$targetMonth->month] ?? 5;

            $this->command->info("Creating {$projectsForMonth} projects for {$targetMonth->format('M Y')}");

            for ($i = 0; $i < $projectsForMonth && $projectsCreated < self::TOTAL_PROJECTS; $i++) {
                // Determine status based on remaining targets
                $status = $this->determineStatus($statusCounts, $targetOngoing, $targetExited, $projectsCreated);

                // Generate realistic dates
                $dates = $this->generateRealisticDates($faker, $targetMonth, $status);

                // Create project with realistic data
                $project = $this->createProject($faker, [
                    'developer_id' => $faker->randomElement($developerIds),
                    'type' => $faker->randomElement($propertyTypes),
                    'investment_type' => $faker->randomElement($investmentTypes),
                    'stage' => 'planning',
                    'status' => $status,
                    'created_at' => $dates['entry_date'],
                    'updated_at' => $dates['exit_date'] ?? $dates['entry_date'],
                    'entry_date' => $dates['entry_date'],
                    'exit_date' => $dates['exit_date'],
                ]);

                $statusCounts[$status]++;
                $projectsCreated++;
            }
        }

        $this->command->info("Created {$projectsCreated} projects:");
        $this->command->info("- On-going: {$statusCounts['on-going']}");
        $this->command->info("- Exited: {$statusCounts['exited']}");
    }

    /**
     * Determine project status based on targets and current counts
     */
    private function determineStatus(array $statusCounts, int $targetOngoing, int $targetExited, int $projectsCreated): string
    {
        $remainingProjects = self::TOTAL_PROJECTS - $projectsCreated;
        $remainingOngoing = $targetOngoing - $statusCounts['on-going'];
        $remainingExited = $targetExited - $statusCounts['exited'];

        // If we've reached the target for one status, use the other
        if ($remainingOngoing <= 0) {
            return 'exited';
        }
        if ($remainingExited <= 0) {
            return 'on-going';
        }

        // Otherwise, use weighted random selection
        $ongoingWeight = $remainingOngoing / $remainingProjects;
        return rand(1, 100) <= ($ongoingWeight * 100) ? 'on-going' : 'exited';
    }

    /**
     * Generate realistic entry and exit dates
     */
    private function generateRealisticDates(Generator $faker, Carbon $targetMonth, string $status): array
    {
        // Entry date within the target month
        $monthStart = $targetMonth->copy()->startOfMonth();
        $monthEnd = $targetMonth->copy()->endOfMonth();
        $entryDate = Carbon::instance($faker->dateTimeBetween($monthStart, $monthEnd));

        $exitDate = null;
        if ($status === 'exited') {
            // Exit date between 1-8 months after entry (realistic project duration)
            $minExitDate = $entryDate->copy()->addMonth();
            $maxExitDate = min($entryDate->copy()->addMonths(8), Carbon::now());

            if ($maxExitDate->gt($minExitDate)) {
                $exitDate = Carbon::instance($faker->dateTimeBetween($minExitDate, $maxExitDate));
            } else {
                $exitDate = $maxExitDate;
            }

            // Ensure exit date is always after entry date
            if ($exitDate->lte($entryDate)) {
                $exitDate = $entryDate->copy()->addDay();
            }
        }

        return [
            'entry_date' => $entryDate,
            'exit_date' => $exitDate,
        ];
    }

    /**
     * Create a project with realistic data
     */
    private function createProject(Generator $faker, array $overrides = []): Project
    {
        $uuid = (string) Str::uuid();
        $baseData = [
            'id' => $uuid,
            'key' => $uuid,
            'title' => $this->generateRealisticProjectName($faker),
            'location' => $this->generateRealisticLocation($faker),
            'type' => 'apartment', // Will be overridden
            'area' => $faker->numberBetween(800, 5000),
            'bedrooms' => $faker->numberBetween(1, 4),
            'bathrooms' => $faker->numberBetween(1, 3),
            'status' => 'available', // Will be overridden
            'stage' => 'planning', // Will be overridden
            'investment_type' => 'buy_to_let', // Will be overridden
            'developer_id' => null, // Will be overridden
        ];

        return Project::create(array_merge($baseData, $overrides));
    }

    /**
     * Generate realistic project names
     */
    private function generateRealisticProjectName(Generator $faker): string
    {
        $prefixes = ['The', 'Royal', 'Grand', 'Premium', 'Elite', 'Modern', 'Urban', 'City'];
        $types = ['Residences', 'Towers', 'Gardens', 'Heights', 'Plaza', 'Square', 'Court', 'Park'];
        $locations = ['Downtown', 'Marina', 'Hills', 'Bay', 'Central', 'West', 'East', 'North'];

        $patterns = [
            '{prefix} {location} {type}',
            '{location} {type}',
            '{prefix} {type}',
            '{location} {prefix} {type}',
        ];

        $pattern = $faker->randomElement($patterns);

        return strtr($pattern, [
            '{prefix}' => $faker->randomElement($prefixes),
            '{type}' => $faker->randomElement($types),
            '{location}' => $faker->randomElement($locations),
        ]);
    }

    /**
     * Generate realistic locations
     */
    private function generateRealisticLocation(Generator $faker): string
    {
        $areas = [
            'Downtown District',
            'Marina Bay',
            'Business Bay',
            'City Center',
            'Waterfront',
            'Financial District',
            'Old Town',
            'New Town',
            'Harbor View',
            'Park Avenue',
            'Central Plaza',
            'Riverside',
            'Hillside',
            'Beachfront',
            'Metropolitan',
            'Urban Core'
        ];

        return $faker->randomElement($areas);
    }

    /**
     * Get configuration options with fallbacks
     */
    private function getConfigurationOptions(string $category): array
    {
        try {
            $options = array_keys(SystemConfiguration::getOptions($category));

            if (empty($options)) {
                return $this->getDefaultOptions($category);
            }

            return $options;
        } catch (\Exception $e) {
            $this->command->warn("Could not load {$category} from configuration. Using defaults.");
            return $this->getDefaultOptions($category);
        }
    }

    /**
     * Get default options if configuration is not available
     */
    private function getDefaultOptions(string $category): array
    {
        return match ($category) {
            'property_types' => ['apartment', 'villa', 'townhouse', 'penthouse'],
            'investment_types' => ['buy_to_let', 'flip', 'development', 'commercial'],
            'project_stages' => ['delivered', 'completed', 'construction', 'planning'],
            default => ['default'],
        };
    }
}
