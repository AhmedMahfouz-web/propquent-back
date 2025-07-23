<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Repositories\ProjectTransactionRepository;
use App\Repositories\ReportCacheRepository;
use App\Services\ProjectTransactionReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class ProjectTransactionReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $projectTransactionRepository;
    protected $reportCacheRepository;
    protected $projectTransactionReportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectTransactionRepository = Mockery::mock(ProjectTransactionRepository::class);
        $this->reportCacheRepository = Mockery::mock(ReportCacheRepository::class);

        $this->projectTransactionReportService = new ProjectTransactionReportService(
            $this->projectTransactionRepository,
            $this->reportCacheRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGenerateProjectTransactionReportWithCachedData()
    {
        $filters = ['project_id' => 1];
        $reportKey = md5(serialize($filters));
        $cachedReport = ['cached' => true];

        $this->reportCacheRepository->shouldReceive('getCachedReport')
            ->once()
            ->with('project_transaction', $reportKey)
            ->andReturn($cachedReport);

        $result = $this->projectTransactionReportService->generateProjectTransactionReport($filters);

        $this->assertEquals($cachedReport, $result);
    }

    public function testGenerateProjectTransactionReportWithoutCachedData()
    {
        $projectId = 1;
        $filters = ['project_id' => $projectId];
        $reportKey = md5(serialize($filters));

        $this->reportCacheRepository->shouldReceive('getCachedReport')
            ->once()
            ->with('project_transaction', $reportKey)
            ->andReturn(null);

        $startDate = Carbon::now()->startOfYear()->subYear();
        $endDate = Carbon::now();

        $monthlyData = collect([
            [
                'year_month' => '2025-06',
                'total_revenue' => 5000,
                'total_expenses' => 3000,
                'net_cash_flow' => 2000,
            ],
            [
                'year_month' => '2025-07',
                'total_revenue' => 6000,
                'total_expenses' => 3500,
                'net_cash_flow' => 2500,
            ],
        ]);

        $this->projectTransactionRepository->shouldReceive('getMonthlyDataForRange')
            ->once()
            ->with(Mockery::type(Carbon::class), Mockery::type(Carbon::class), $projectId)
            ->andReturn($monthlyData);

        $categoryTotals = [
            'revenue_categories' => [
                'sales' => 8000,
                'rental' => 3000,
            ],
            'expense_categories' => [
                'maintenance' => 4000,
                'administrative' => 2500,
            ],
        ];

        $project = Mockery::mock(Project::class);
        $project->shouldReceive('getAttribute')->with('id')->andReturn($projectId);
        $project->shouldReceive('getAttribute')->with('key')->andReturn('PRJ001');
        $project->shouldReceive('getAttribute')->with('title')->andReturn('Test Project');

        Project::shouldReceive('find')
            ->once()
            ->with($projectId)
            ->andReturn($project);

        $this->projectTransactionRepository->shouldReceive('getMonthlyRevenueByCategory')
            ->once()
            ->with($projectId)
            ->andReturn(collect([
                ['transaction_category' => 'sales', 'total_amount' => 8000],
                ['transaction_category' => 'rental', 'total_amount' => 3000],
            ]));

        $this->projectTransactionRepository->shouldReceive('getMonthlyExpensesByCategory')
            ->once()
            ->with($projectId)
            ->andReturn(collect([
                ['transaction_category' => 'maintenance', 'total_amount' => 4000],
                ['transaction_category' => 'administrative', 'total_amount' => 2500],
            ]));

        $this->reportCacheRepository->shouldReceive('cacheReport')
            ->once()
            ->with('project_transaction', $reportKey, Mockery::type('array'), 60);

        $result = $this->projectTransactionReportService->generateProjectTransactionReport($filters);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('monthly_data', $result);
        $this->assertArrayHasKey('category_totals', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('filters', $result);
        $this->assertArrayHasKey('generated_at', $result);
        $this->assertArrayHasKey('project', $result);
    }

    public function testCalculateMonthlyRevenueTrends()
    {
        $projectId = 1;
        $months = 12;
        $reportKey = "monthly_revenue_trends_{$months}_{$projectId}";

        $this->reportCacheRepository->shouldReceive('getCachedReport')
            ->once()
            ->with('project_transaction', $reportKey)
            ->andReturn(null);

        $monthlyData = collect([
            [
                'year_month' => '2025-06',
                'month_name' => 'Jun 2025',
                'total_revenue' => 5000,
                'total_expenses' => 3000,
                'net_cash_flow' => 2000,
            ],
            [
                'year_month' => '2025-07',
                'month_name' => 'Jul 2025',
                'total_revenue' => 6000,
                'total_expenses' => 3500,
                'net_cash_flow' => 2500,
            ],
        ]);

        $this->projectTransactionRepository->shouldReceive('getMonthlyDataForRange')
            ->once()
            ->with(Mockery::type(Carbon::class), Mockery::type(Carbon::class), $projectId)
            ->andReturn($monthlyData);

        $this->reportCacheRepository->shouldReceive('cacheReport')
            ->once()
            ->with('project_transaction', $reportKey, Mockery::type('array'), 60);

        $result = $this->projectTransactionReportService->calculateMonthlyRevenueTrends($months, $projectId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('months', $result);
        $this->assertArrayHasKey('revenue', $result);
        $this->assertArrayHasKey('expenses', $result);
        $this->assertArrayHasKey('net_cash_flow', $result);
    }
}
