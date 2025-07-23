<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Services\ProjectReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProjectReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ProjectReportService $reportService;
eveloper $developer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();

        // Get report service
        $this->reportService = app(ProjectReportService::class);

        // Clear cache before each test
        Cache::flush();

        // Create test data
        $this->createTestData();
    }

    /** @test */
    public function authenticated_user_can_access_reports_page()
    {
        // Act & Assert
        $this->actingAs($this->user)
            ->get('/admin/reports/projects')
            ->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_reports_page()
    {
        // Act & Assert
        $this->get('/admin/reports/projects')
            ->assertRedirect('/admin/login');
    }

    /** @test */
    public function reports_page_displays_monthly_data()
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/projects');

        // Assert
        $response->assertStatus(200)
            ->assertSee('Monthly Report')
            ->assertSee(Carbon::now()->format('M Y'))
            ->assertSee('New Projects')
            ->assertSee('Exited Projects')
            ->assertSee('Total Investment');
    }

    /** @test */
    public function report_service_generates_monthly_data_correctly()
    {
        // Act
        $report = $this->reportService->generateMonthlyReport();

        // Assert
        $this->assertIsArray($report);
        $this->assertArrayHasKey('months', $report);
        $this->assertArrayHasKey('metrics', $report);
        $this->assertArrayHasKey('summary', $report);

        // Check that current month is included
        $currentMonth = Carbon::now()->format('Y-m');
        $this->assertArrayHasKey($currentMonth, $report['metrics']);
    }

    /** @test */
    public function report_includes_correct_project_metrics()
    {
        // Act
        $report = $this->reportService->generateMonthlyReport();
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMetrics = $report['metrics'][$currentMonth];

        // Assert
        $this->assertArrayHasKey('new_projects', $currentMetrics);
        $this->assertArrayHasKey('exited_projects', $currentMetrics);
        $this->assertArrayHasKey('ongoing_projects', $currentMetrics);
        $this->assertArrayHasKey('total_investment', $currentMetrics);
        $this->assertArrayHasKey('revenue_generated', $currentMetrics);
        $this->assertArrayHasKey('roi_percentage', $currentMetrics);

        // Check that metrics have correct values based on test data
        $this->assertGreaterThanOrEqual(0, $currentMetrics['new_projects']);
        $this->assertGreaterThanOrEqual(0, $currentMetrics['total_investment']);
    }

    /** @test */
    public function report_filtering_works_correctly()
    {
        // Arrange
        $filters = [
            'developer_id' => $this->developer->id,
            'start_date' => Carbon::now()->subMonths(6)->format('Y-m-d'),
            'end_date' => Carbon::now()->format('Y-m-d'),
        ];

        // Act
        $report = $this->reportService->generateMonthlyReport($filters);

        // Assert
        $this->assertIsArray($report);
        $this->assertEquals($filters['start_date'], $report['period']['start']);
        $this->assertEquals($filters['end_date'], $report['period']['end']);

        // Should only include 7 months (6 months + current)
        $this->assertCount(7, $report['months']);
    }

    /** @test */
    public function user_can_filter_reports_by_developer()
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/projects?developer_id=' . $this->developer->id);

        // Assert
        $response->assertStatus(200)
            ->assertSee($this->developer->name);
    }

    /** @test */
    public function user_can_filter_reports_by_date_range()
    {
        // Arrange
        $startDate = Carbon::now()->subMonths(3)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        // Act
        $response = $this->actingAs($this->user)
            ->get("/admin/reports/projects?start_date={$startDate}&end_date={$endDate}");

        // Assert
        $response->assertStatus(200);
        // Should show filtered date range
    }

    /** @test */
    public function user_can_export_report_data()
    {
        // Act
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/projects/export?format=csv');

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function report_caching_works_correctly()
    {
        // Act - First call should hit database and cache result
        $start = microtime(true);
        $report1 = $this->reportService->generateMonthlyReport();
        $time1 = microtime(true) - $start;

        // Second call should be faster (from cache)
        $start = microtime(true);
        $report2 = $this->reportService->generateMonthlyReport();
        $time2 = microtime(true) - $start;

        // Assert
        $this->assertEquals($report1, $report2);
        // Second call should be significantly faster
        $this->assertLessThan($time1, $time2 + 0.001); // Allow small margin for timing variations
    }

    /** @test */
    public function report_cache_is_cleared_when_data_changes()
    {
        // Arrange - Generate initial report to populate cache
        $initialReport = $this->reportService->generateMonthlyReport();
        $currentMonth = Carbon::now()->format('Y-m');
        $initialNewProjects = $initialReport['metrics'][$currentMonth]['new_projects'];

        // Act - Create new project (should trigger cache clearing via observer)
        Project::factory()->create([
            'developer_id' => $this->developer->id,
            'status' => 'on-going',
            'created_at' => Carbon::now(),
        ]);

        // Generate report again
        $updatedReport = $this->reportService->generateMonthlyReport();
        $updatedNewProjects = $updatedReport['metrics'][$currentMonth]['new_projects'];

        // Assert - New projects count should be updated
        $this->assertGreaterThan($initialNewProjects, $updatedNewProjects);
    }

    /** @test */
    public function report_handles_empty_data_gracefully()
    {
        // Arrange - Clear all test data
        Project::query()->delete();
        ProjectTransaction::query()->delete();

        // Act
        $report = $this->reportService->generateMonthlyReport();

        // Assert
        $this->assertIsArray($report);
        $currentMonth = Carbon::now()->format('Y-m');
        $currentMetrics = $report['metrics'][$currentMonth];

        $this->assertEquals(0, $currentMetrics['new_projects']);
        $this->assertEquals(0, $currentMetrics['total_investment']);
        $this->assertEquals(0, $currentMetrics['revenue_generated']);
    }

    /** @test */
    public function report_calculates_roi_correctly()
    {
        // Arrange - Create specific investment and revenue data
        $project = Project::factory()->create([
            'developer_id' => $this->developer->id,
            'status' => 'on-going',
        ]);

        // Investment transaction
        ProjectTransaction::factory()->create([
            'project_id' => $project->id,
            'type' => 'investment',
            'amount' => 100000,
            'created_at' => Carbon::now(),
        ]);

        // Revenue transaction
        ProjectTransaction::factory()->create([
            'project_id' => $project->id,
            'type' => 'revenue',
            'amount' => 120000,
            'created_at' => Carbon::now(),
        ]);

        // Act
        $report = $this->reportService->generateMonthlyReport();
        $currentMonth = Carbon::now()->format('Y-m');
        $roi = $report['metrics'][$currentMonth]['roi_percentage'];

        // Assert - ROI should be 20% ((120000 - 100000) / 100000 * 100)
        $this->assertEquals(20.0, $roi);
    }

    /** @test */
    public function report_summary_calculates_totals_correctly()
    {
        // Act
        $report = $this->reportService->generateMonthlyReport();
        $summary = $report['summary'];

        // Assert
        $this->assertArrayHasKey('total_new_projects', $summary);
        $this->assertArrayHasKey('total_exited_projects', $summary);
        $this->assertArrayHasKey('total_investment', $summary);
        $this->assertArrayHasKey('total_revenue', $summary);
        $this->assertArrayHasKey('average_roi', $summary);

        $this->assertIsNumeric($summary['total_new_projects']);
        $this->assertIsNumeric($summary['total_investment']);
    }

    /** @test */
    public function report_supports_different_date_ranges()
    {
        // Test different date ranges
        $testCases = [
            ['months' => 3, 'expected_count' => 4], // 3 months + current
            ['months' => 6, 'expected_count' => 7], // 6 months + current
            ['months' => 12, 'expected_count' => 13], // 12 months + current
        ];

        foreach ($testCases as $testCase) {
            // Arrange
            $filters = [
                'start_date' => Carbon::now()->subMonths($testCase['months'])->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
            ];

            // Act
            $report = $this->reportService->generateMonthlyReport($filters);

            // Assert
            $this->assertCount($testCase['expected_count'], $report['months']);
        }
    }

    /** @test */
    public function report_performance_is_optimized()
    {
        // Arrange - Create more test data to test performance
        Project::factory()->count(50)->create([
            'developer_id' => $this->developer->id,
            'created_at' => Carbon::now()->subMonths(rand(0, 11)),
        ]);

        ProjectTransaction::factory()->count(200)->create([
            'project_id' => Project::inRandomOrder()->first()->id,
            'created_at' => Carbon::now()->subMonths(rand(0, 11)),
        ]);

        // Act - Measure report generation time
        $start = microtime(true);
        $report = $this->reportService->generateMonthlyReport();
        $executionTime = microtime(true) - $start;

        // Assert - Should complete within reasonable time (less than 2 seconds)
        $this->assertLessThan(2.0, $executionTime);
        $this->assertIsArray($report);
    }

    /**
     * Create test data for reports
     */
    private function createTestData(): void
    {
        // Create developer
        $this->developer = Developer::factory()->create([
            'name' => 'Test Developer',
            'is_active' => true,
        ]);

        // Create system configurations
        SystemConfiguration::factory()->create([
            'category' => 'project_statuses',
            'key' => 'on-going',
            'value' => 'on-going',
            'label' => 'On Going',
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'project_statuses',
            'key' => 'exited',
            'value' => 'exited',
            'label' => 'Exited',
        ]);

        // Create projects with different dates
        $dates = [
            Carbon::now(),
            Carbon::now()->subMonth(),
            Carbon::now()->subMonths(2),
        ];

        foreach ($dates as $date) {
            // Create projects
            Project::factory()->count(2)->create([
                'developer_id' => $this->developer->id,
                'status' => 'on-going',
                'created_at' => $date,
            ]);

            Project::factory()->create([
                'developer_id' => $this->developer->id,
                'status' => 'exited',
                'created_at' => $date->copy()->subDays(15),
                'updated_at' => $date,
            ]);
        }

        // Create transactions
        $projects = Project::all();
        foreach ($projects as $project) {
            ProjectTransaction::factory()->create([
                'project_id' => $project->id,
                'type' => 'investment',
                'amount' => rand(50000, 200000),
                'created_at' => $project->created_at,
            ]);

            if (rand(0, 1)) {
                ProjectTransaction::factory()->create([
                    'project_id' => $project->id,
                    'type' => 'revenue',
                    'amount' => rand(60000, 250000),
                    'created_at' => $project->created_at->copy()->addDays(30),
                ]);
            }
        }
    }
}
    protec
