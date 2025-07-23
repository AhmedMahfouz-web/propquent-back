<?php

namespace Tests\Feature\Pages;

use App\Filament\Pages\Reports\ProjectTransactionReport;
use App\Models\Admin;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Services\ProjectTransactionReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ProjectTransactionReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for authentication
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admins');
    }

    public function testProjectTransactionReportPageLoads()
    {
        // Mock the ProjectTransactionReportService
        $mockService = Mockery::mock(ProjectTransactionReportService::class);
        $mockService->shouldReceive('generateProjectTransactionReport')
            ->andReturn([
                'monthly_data' => [],
                'category_totals' => [
                    'revenue_categories' => [],
                    'expense_categories' => [],
                ],
                'summary' => [
                    'total_revenue' => 0,
                    'total_expenses' => 0,
                    'net_cash_flow' => 0,
                ],
                'filters' => [],
                'generated_at' => Carbon::now()->toDateTimeString(),
            ]);

        $this->app->instance(ProjectTransactionReportService::class, $mockService);

        // Test that the page loads
        Livewire::test(ProjectTransactionReport::class)
            ->assertSuccessful();
    }

    public function testGenerateReportAction()
    {
        // Create test data
        $project = Project::factory()->create();

        // Mock the ProjectTransactionReportService
        $mockService = Mockery::mock(ProjectTransactionReportService::class);
        $mockService->shouldReceive('generateProjectTransactionReport')
            ->andReturn([
                'monthly_data' => [
                    [
                        'year_month' => Carbon::now()->subMonth()->format('Y-m'),
                        'month_name' => Carbon::now()->subMonth()->format('M Y'),
                        'total_revenue' => 5000,
                        'total_expenses' => 3000,
                        'net_cash_flow' => 2000,
                    ],
                    [
                        'year_month' => Carbon::now()->format('Y-m'),
                        'month_name' => Carbon::now()->format('M Y'),
                        'total_revenue' => 6000,
                        'total_expenses' => 3500,
                        'net_cash_flow' => 2500,
                    ],
                ],
                'category_totals' => [
                    'revenue_categories' => [
                        'sales' => 8000,
                        'rental' => 3000,
                    ],
                    'expense_categories' => [
                        'maintenance' => 4000,
                        'administrative' => 2500,
                    ],
                ],
                'summary' => [
                    'total_revenue' => 11000,
                    'total_expenses' => 6500,
                    'net_cash_flow' => 4500,
                ],
                'filters' => [
                    'project_id' => $project->id,
                    'date_range' => [
                        'from' => Carbon::now()->startOfYear()->subYear()->format('Y-m-d'),
                        'until' => Carbon::now()->format('Y-m-d'),
                    ],
                ],
                'generated_at' => Carbon::now()->toDateTimeString(),
                'project' => [
                    'id' => $project->id,
                    'key' => $project->key,
                    'title' => $project->title,
                ],
            ]);

        $this->app->instance(ProjectTransactionReportService::class, $mockService);

        // Test the generate report action
        Livewire::test(ProjectTransactionReport::class)
            ->set('form.project_id', $project->id)
            ->call('generateReport')
            ->assertSet('reportData.summary.total_revenue', 11000)
            ->assertSet('reportData.summary.total_expenses', 6500)
            ->assertSet('reportData.summary.net_cash_flow', 4500);
    }
}
