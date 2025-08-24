<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\SystemConfiguration;
use Faker\Generator as Faker;
use Carbon\Carbon;

class ProjectTransactionSeeder extends Seeder
{
    /**
     * Average number of transactions per project (varies by project age)
     */
    private const MIN_TRANSACTIONS_PER_PROJECT = 2;
    private const MAX_TRANSACTIONS_PER_PROJECT = 8;

    /**
     * Transaction amount ranges by type (in USD)
     */
    private const AMOUNT_RANGES = [
        'investment' => [50000, 500000],
        'expense' => [2000, 50000],
        'revenue' => [10000, 150000],
        'maintenance' => [500, 15000],
        'fee' => [200, 8000],
        'profit' => [5000, 100000],
        'sale' => [100000, 1000000],
        'purchase' => [25000, 300000],
        'tax' => [1000, 25000],
        'dividend' => [5000, 50000],
    ];

    /**
     * Payment method distribution
     */
    private const PAYMENT_METHOD_DISTRIBUTION = [
        'bank_transfer' => 0.40,
        'wire_transfer' => 0.25,
        'check' => 0.15,
        'cash' => 0.10,
        'credit_card' => 0.05,
        'cryptocurrency' => 0.05,
    ];

    public function run(): void
    {
        $faker = app(Faker::class);
        $projects = Project::with('developer')->get();

        if ($projects->isEmpty()) {
            $this->command->info('No projects found. Please run ProjectSeeder first.');
            return;
        }

        // Use default options instead of configuration
        $transactionTypes = $this->getDefaultOptions('project_transaction_types');
        $transactionStatuses = $this->getDefaultOptions('transaction_statuses');
        $transactionMethods = $this->getDefaultOptions('transaction_methods');
        $transactionServing = $this->getDefaultOptions('transaction_serving');

        $this->command->info('Creating realistic transaction data...');

        $totalTransactions = 0;
        $transactionsByType = [];

        // Pre-calculate total investments to avoid N+1 queries
        // Pre-calculate total investments to avoid N+1 queries.
        // Note: 'investment' is mapped to 'expense' under the new schema.
        $totalInvestments = ProjectTransaction::where('financial_type', 'expense')
            ->groupBy('project_key')
            ->selectRaw('project_key, SUM(amount) as total')
            ->pluck('total', 'project_key');

        foreach ($projects as $project) {
            $projectAge = $project->created_at->diffInMonths(Carbon::now());
            $transactionCount = $this->calculateTransactionCount($projectAge, $project->status);

            $this->command->info("Creating {$transactionCount} transactions for project: {$project->name}");

            // Create initial investment transaction (always first)
            $this->createInitialInvestment($faker, $project, $transactionMethods, $transactionServing);
            $totalTransactions++;

            // Create additional transactions spread over project lifetime
            for ($i = 1; $i < $transactionCount; $i++) {
                $transaction = $this->createRealisticTransaction(
                    $faker,
                    $project,
                    $transactionTypes,
                    $transactionStatuses,
                    $transactionMethods,
                    $transactionServing,
                    $i,
                    $transactionCount
                );

                $financialType = $transaction->financial_type;
                $transactionsByType[$financialType] = ($transactionsByType[$financialType] ?? 0) + 1;
                $totalTransactions++;
            }

            // Create exit transaction for exited projects
            if ($project->status === 'exited' && $project->exit_date) {
                $this->createExitTransaction($faker, $project, $transactionServing, $totalInvestments);
                $totalTransactions++;
            }
        }

        $this->command->info("Created {$totalTransactions} transactions:");
        foreach ($transactionsByType as $type => $count) {
            $this->command->info("- {$type}: {$count}");
        }
    }

    /**
     * Calculate number of transactions based on project age and status
     */
    private function calculateTransactionCount(int $projectAgeMonths, string $status): int
    {
        $baseCount = self::MIN_TRANSACTIONS_PER_PROJECT;

        // More transactions for older projects
        $ageBonus = min(floor((int) $projectAgeMonths / 2), 4);

        // More transactions for exited projects (complete lifecycle)
        $statusBonus = $status === 'exited' ? 2 : 0;

        $totalCount = $baseCount + $ageBonus + $statusBonus;

        return min($totalCount, self::MAX_TRANSACTIONS_PER_PROJECT);
    }

    /**
     * Create initial investment transaction
     */
    private function createInitialInvestment(
        Faker $faker,
        Project $project,
        array $transactionMethods,
        array $transactionServing
    ): ProjectTransaction {
        $investmentAmount = $this->calculateInvestmentAmount($faker, $project);

        try {
            $maxDate = Carbon::now()->subDays(1);
            $transactionDate = $project->created_at->copy()->addDays($faker->numberBetween(0, 7));
            $dueDate = $project->created_at->copy()->addDays($faker->numberBetween(30, 60));
            
            // Ensure dates are not in the future
            if ($transactionDate->gt($maxDate)) {
                $transactionDate = $maxDate;
            }
            if ($dueDate->gt($maxDate)) {
                $dueDate = $maxDate;
            }
            
            return ProjectTransaction::create([
                'project_key' => $project->key,
                'financial_type' => 'expense', // Mapped from 'investment'
                'amount' => $investmentAmount,
                'transaction_date' => $transactionDate,
                'due_date' => $dueDate,
                'status' => 'done',
                'method' => $faker->randomElement($transactionMethods),
                'serving' => $faker->randomElement($transactionServing),
                'reference_no' => $this->generateReferenceNumber($faker),
                'note' => "Initial project funding",
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ]);
        } catch (\Exception $e) {
            $this->command->error("Error creating initial investment for project {$project->key}: " . $e->getMessage());
            $this->command->error("Data: " . json_encode([
                'project_key' => $project->key,
                'financial_type' => 'expense',
                'amount' => $investmentAmount,
                'method' => $faker->randomElement($transactionMethods),
                'serving' => $faker->randomElement($transactionServing),
            ]));
            throw $e;
        }
    }

    /**
     * Create realistic transaction based on project timeline
     */
    private function createRealisticTransaction(
        Faker $faker,
        Project $project,
        array $transactionTypes,
        array $transactionStatuses,
        array $transactionMethods,
        array $transactionServing,
        int $transactionIndex,
        int $totalTransactions
    ): ProjectTransaction {
        // Determine transaction type based on project phase
        $type = $this->selectTransactionType($faker, $project, $transactionIndex, $totalTransactions);

        // Calculate realistic transaction date
        $transactionDate = $this->calculateTransactionDate($faker, $project, $transactionIndex, $totalTransactions);

        // Generate amount based on type and project
        $amount = $this->generateTransactionAmount($faker, $type, $project);

        // Select status (more recent transactions more likely to be pending)
        $status = $this->selectTransactionStatus($faker, $transactionDate);

        $financialType = $this->mapToFinancialType($type);

        try {
            return ProjectTransaction::create([
                'project_key' => $project->key,
                'financial_type' => $financialType,
                'amount' => $amount,
                'transaction_date' => $transactionDate,
                'due_date' => $this->calculateDueDate($faker, $transactionDate, $type),
                'status' => $status,
                'method' => $this->selectPaymentMethod($faker, $amount),
                'serving' => $faker->randomElement($transactionServing),
                'reference_no' => $this->generateReferenceNumber($faker),
                'note' => $faker->optional(0.4)->sentence(),
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate->addDays($faker->numberBetween(0, 5)),
            ]);
        } catch (\Exception $e) {
            $this->command->error("Error creating realistic transaction for project {$project->key}: " . $e->getMessage());
            $this->command->error("Data: " . json_encode([
                'project_key' => $project->key,
                'financial_type' => $financialType,
                'amount' => $amount,
                'method' => $this->selectPaymentMethod($faker, $amount),
                'serving' => $faker->randomElement($transactionServing),
                'status' => $status,
            ]));
            throw $e;
        }
    }

    /**
     * Create exit transaction for completed projects
     */
    private function createExitTransaction(
        Faker $faker,
        Project $project,
        array $transactionServing,
        $totalInvestments
    ): ProjectTransaction {
        $exitAmount = $this->calculateExitAmount($faker, $project, $totalInvestments);

        $maxDate = Carbon::now()->subDays(1);
        $exitDate = $project->exit_date ?? $project->updated_at;
        $dueDate = $exitDate->copy()->addDays(30);
        
        // Ensure dates are not in the future
        if ($exitDate->gt($maxDate)) {
            $exitDate = $maxDate;
        }
        if ($dueDate->gt($maxDate)) {
            $dueDate = $maxDate;
        }

        return ProjectTransaction::create([
            'project_key' => $project->key,
            'financial_type' => 'revenue', // Mapped from 'sale'
            'amount' => $exitAmount,
            'transaction_date' => $exitDate,
            'due_date' => $dueDate,
            'status' => 'done',
            'method' => $faker->randomElement(['bank_transfer', 'wire_transfer']),
            'serving' => $faker->randomElement($transactionServing),
            'reference_no' => $this->generateReferenceNumber($faker),
            'note' => "Final project sale transaction",
            'created_at' => $exitDate,
            'updated_at' => $exitDate,
        ]);
    }

    /**
     * Calculate realistic investment amount based on project characteristics
     */
    private function calculateInvestmentAmount(Faker $faker, Project $project): float
    {
        $baseAmount = 100000; // Base investment

        // Adjust based on property type
        $typeMultiplier = match ($project->type) {
            'penthouse' => 3.0,
            'villa' => 2.5,
            'townhouse' => 1.8,
            'apartment' => 1.0,
            default => 1.2,
        };

        // Adjust based on area
        $areaMultiplier = ($project->area ?? 1000) / 1000;

        // Add some randomness
        $randomMultiplier = $faker->randomFloat(2, 0.8, 1.4);

        return round($baseAmount * $typeMultiplier * $areaMultiplier * $randomMultiplier, 2);
    }

    /**
     * Select transaction type based on project phase and randomness
     */
    private function selectTransactionType(Faker $faker, Project $project, int $index, int $total): string
    {
        $phase = $index / $total;

        // Early phase (0-0.3): More expenses and fees
        if ($phase <= 0.3) {
            return $faker->randomElement(['expense', 'fee', 'maintenance']);
        }

        // Mid phase (0.3-0.7): Mixed transactions
        if ($phase <= 0.7) {
            return $faker->randomElement(['expense', 'revenue', 'maintenance', 'fee']);
        }

        // Late phase (0.7-1.0): More revenue
        return $faker->randomElement(['revenue', 'profit', 'expense']);
    }

    /**
     * Calculate transaction date based on project timeline
     */
    private function calculateTransactionDate(Faker $faker, Project $project, int $index, int $total): Carbon
    {
        $projectStart = $project->created_at;
        $projectEnd = $project->exit_date ?? Carbon::now();

        // Ensure we don't generate future dates
        $maxDate = Carbon::now()->subDays(1);
        if ($projectEnd->gt($maxDate)) {
            $projectEnd = $maxDate;
        }
        if ($projectStart->gt($maxDate)) {
            $projectStart = $maxDate->copy()->subMonths(6);
        }

        $totalDays = $projectStart->diffInDays($projectEnd);
        $transactionDay = ($index / $total) * $totalDays;

        // Add some randomness
        $randomOffset = $faker->numberBetween(-7, 7);

        $calculatedDate = $projectStart->copy()->addDays($transactionDay + $randomOffset);
        
        // Ensure the calculated date is not in the future
        return $calculatedDate->gt($maxDate) ? $maxDate : $calculatedDate;
    }

    /**
     * Generate transaction amount based on type and project
     */
    private function generateTransactionAmount(Faker $faker, string $type, Project $project): float
    {
        $range = self::AMOUNT_RANGES[$type] ?? [1000, 10000];
        $baseAmount = $faker->numberBetween($range[0], $range[1]);

        // Adjust based on project size
        $sizeMultiplier = (($project->total_area ?? 1000) / 1000) * 0.5 + 0.5;

        return round($baseAmount * $sizeMultiplier, 2);
    }

    /**
     * Select transaction status based on recency
     */
    private function selectTransactionStatus(Faker $faker, Carbon $transactionDate): string
    {
        $daysAgo = $transactionDate->diffInDays(Carbon::now());

        // Recent transactions can be pending or done
        if ($daysAgo <= 30) {
            return $faker->randomElement(['done', 'pending']);
        }

        // Older transactions are mostly done or cancelled
        return $faker->randomElement(['done', 'done', 'done', 'cancelled']);
    }

    /**
     * Select payment method based on amount
     */
    private function selectPaymentMethod(Faker $faker, float $amount): string
    {
        // For very large amounts, restrict to secure methods
        if ($amount > 100000) {
            return $faker->randomElement(['wire_transfer', 'bank_transfer']);
        }

        // For large amounts, exclude some payment methods
        if ($amount > 10000) {
            $methods = ['bank_transfer', 'wire_transfer', 'check'];
            $weights = array_intersect_key(self::PAYMENT_METHOD_DISTRIBUTION, array_flip($methods));
            return $this->getWeightedRandomElement($faker, $weights);
        }

        // For smaller amounts, use the full distribution
        return $this->getWeightedRandomElement($faker, self::PAYMENT_METHOD_DISTRIBUTION);
    }

    /**
     * Get a random element based on weights
     */
    private function getWeightedRandomElement(Faker $faker, array $weights): string
    {
        $total = array_sum($weights);
        $rand = $faker->randomFloat(2, 0, $total);
        $current = 0;

        foreach ($weights as $key => $weight) {
            $current += $weight;
            if ($rand <= $current) {
                return $key;
            }
        }

        // Fallback to first key
        return array_key_first($weights);
    }

    /**
     * Calculate due date based on transaction type
     */
    private function calculateDueDate(Faker $faker, Carbon $transactionDate, string $type): Carbon
    {
        $daysToAdd = match ($type) {
            'investment' => $faker->numberBetween(30, 60),
            'revenue' => $faker->numberBetween(15, 45),
            'expense' => $faker->numberBetween(7, 30),
            'fee' => $faker->numberBetween(14, 30),
            default => $faker->numberBetween(15, 30),
        };

        return $transactionDate->copy()->addDays($daysToAdd);
    }

    /**
     * Calculate exit amount (typically higher than total investment)
     */
    private function calculateExitAmount(Faker $faker, Project $project, $totalInvestments): float
    {
        // Get total investment from pre-calculated data
        $totalInvestment = $totalInvestments[$project->key] ?? 0;

        if ($totalInvestment == 0) {
            $totalInvestment = 200000; // Fallback
        }

        // Exit amount should be 110% to 180% of total investment
        $multiplier = $faker->randomFloat(2, 1.1, 1.8);

        return round($totalInvestment * $multiplier, 2);
    }

    /**
     * Generate transaction description
     */
    private function generateTransactionDescription(Faker $faker, string $type, Project $project): string
    {
        $descriptions = [
            'investment' => [
                "Investment funding for {$project->name}",
                "Capital injection - {$project->name}",
                "Project financing - {$project->name}",
            ],
            'expense' => [
                "Construction costs - {$project->name}",
                "Material expenses - {$project->name}",
                "Labor costs - {$project->name}",
                "Equipment rental - {$project->name}",
            ],
            'revenue' => [
                "Rental income - {$project->name}",
                "Property revenue - {$project->name}",
                "Lease payment - {$project->name}",
            ],
            'maintenance' => [
                "Property maintenance - {$project->name}",
                "Repair costs - {$project->name}",
                "Facility upkeep - {$project->name}",
            ],
            'fee' => [
                "Management fee - {$project->name}",
                "Legal fees - {$project->name}",
                "Administrative costs - {$project->name}",
            ],
        ];

        $typeDescriptions = $descriptions[$type] ?? ["Transaction - {$project->name}"];

        return $faker->randomElement($typeDescriptions);
    }

    /**
     * Generate reference number
     */
    private function generateReferenceNumber(Faker $faker): string
    {
        $prefix = $faker->randomElement(['TXN', 'REF', 'PAY', 'INV']);
        $number = $faker->numberBetween(100000, 999999);

        return "{$prefix}-{$number}";
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
        } catch (\Exception) {
            $this->command->warn("Could not load {$category} from configuration. Using defaults.");
            return $this->getDefaultOptions($category);
        }
    }

    /**
     * Get default options if configuration is not available
     */
    private function mapToFinancialType(string $type): string
    {
        return match ($type) {
            'revenue', 'profit', 'sale' => 'revenue',
            'expense', 'fee', 'maintenance', 'investment' => 'expense',
            default => 'expense',
        };
    }

    private function getDefaultOptions(string $category): array
    {
        return match ($category) {
            'project_transaction_types' => ['investment', 'expense', 'revenue', 'maintenance', 'fee'],
            'transaction_statuses' => ['done', 'pending', 'cancelled'],
            'transaction_methods' => ['bank_transfer', 'wire_transfer', 'check', 'cash', 'credit_card'],
            'transaction_serving' => ['asset', 'operation'],
            default => ['default'],
        };
    }
}
