<?php

namespace Tests\Unit\Repositories;

use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Repositories\ProjectTransactionRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTransactionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ProjectTransactionRepository();
    }

    public function testGetProjectTransactions()
    {
        // Create a project
        $project = Project::factory()->create();

        // Create some transactions
        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'revenue',
            'amount' => 5000,
            'transaction_date' => Carbon::now()->subMonth(),
        ]);

        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'expense',
            'amount' => 3000,
            'transaction_date' => Carbon::now(),
        ]);

        // Test getting all transactions for the project
        $transactions = $this->repository->getProjectTransactions($project->id);
        $this->assertCount(2, $transactions);

        // Test filtering by financial type
        $revenue = $this->repository->getProjectTransactions($project->id, ['financial_type' => 'revenue']);
        $this->assertCount(1, $revenue);
        $this->assertEquals('revenue', $revenue->first()->financial_type);

        $expenses = $this->repository->getProjectTransactions($project->id, ['financial_type' => 'expense']);
        $this->assertCount(1, $expenses);
        $this->assertEquals('expense', $expenses->first()->financial_type);
    }

    public function testGetMonthlyRevenueByCategory()
    {
        // Create a project
        $project = Project::factory()->create();

        // Create revenue transactions with different categories
        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'revenue',
            'transaction_category' => 'sales',
            'amount' => 3000,
            'transaction_date' => Carbon::now()->subMonth(),
        ]);

        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'revenue',
            'transaction_category' => 'rental',
            'amount' => 2000,
            'transaction_date' => Carbon::now()->subMonth(),
        ]);

        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'revenue',
            'transaction_category' => 'sales',
            'amount' => 4000,
            'transaction_date' => Carbon::now(),
        ]);

        // Test getting revenue by category
        $revenueByCategory = $this->repository->getMonthlyRevenueByCategory($project->id);

        $this->assertCount(2, $revenueByCategory);

        $salesRevenue = $revenueByCategory->firstWhere('transaction_category', 'sales');
        $this->assertNotNull($salesRevenue);
        $this->assertEquals(7000, $salesRevenue->total_amount);

        $rentalRevenue = $revenueByCategory->firstWhere('transaction_category', 'rental');
        $this->assertNotNull($rentalRevenue);
        $this->assertEquals(2000, $rentalRevenue->total_amount);
    }

    public function testGetMonthlyExpensesByCategory()
    {
        // Create a project
        $project = Project::factory()->create();

        // Create expense transactions with different categories
        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'expense',
            'transaction_category' => 'maintenance',
            'amount' => 1500,
            'transaction_date' => Carbon::now()->subMonth(),
        ]);

        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'expense',
            'transaction_category' => 'administrative',
            'amount' => 1000,
            'transaction_date' => Carbon::now()->subMonth(),
        ]);

        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'expense',
            'transaction_category' => 'maintenance',
            'amount' => 2000,
            'transaction_date' => Carbon::now(),
        ]);

        // Test getting expenses by category
        $expensesByCategory = $this->repository->getMonthlyExpensesByCategory($project->id);

        $this->assertCount(2, $expensesByCategory);

        $maintenanceExpenses = $expensesByCategory->firstWhere('transaction_category', 'maintenance');
        $this->assertNotNull($maintenanceExpenses);
        $this->assertEquals(3500, $maintenanceExpenses->total_amount);

        $administrativeExpenses = $expensesByCategory->firstWhere('transaction_category', 'administrative');
        $this->assertNotNull($administrativeExpenses);
        $this->assertEquals(1000, $administrativeExpenses->total_amount);
    }

    public function testCalculateMonthlyNetCashFlow()
    {
        // Create a project
        $project = Project::factory()->create();

        // Create revenue and expense transactions
        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'revenue',
            'amount' => 5000,
            'transaction_date' => Carbon::now(),
        ]);

        ProjectTransaction::factory()->create([
            'project_key' => $project->key,
            'financial_type' => 'expense',
            'amount' => 3000,
            'transaction_date' => Carbon::now(),
        ]);

        // Test calculating net cash flow
        $netCashFlow = $this->repository->calculateMonthlyNetCashFlow($project->id, Carbon::now()->format('Y-m'));

        $this->assertEquals(2000, $netCashFlow);
    }
}
