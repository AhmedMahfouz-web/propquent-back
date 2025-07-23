<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Project;
use App\Models\ProjectTransaction;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Services\CacheService;
use App\Services\ConfigurationService;
use App\Services\ProjectReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Developer $developer;
    protected ConfigurationService $configService;
    protected ProjectReportService $reportService;
    protected CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();

        // Get services
        $this->configService = app(ConfigurationService::class);
        $this->reportService = app(ProjectReportService::class);
        $this->cacheService = app(CacheService::class);

        // Clear cache before each test
        Cache::flush();

        // Create test data
        $this->createCompleteTestData();
    }

    /** @test */
    public function complete_configuration_workflow_works_end_to_end()
    {
        // 1. Create new configuration through settings page
        $newConfigData = [
            'category' => 'project_statuses',
            'key' => 'new_status',
            'value' => 'new_status',
            'label' => 'New Status',
            'description' => 'A new project status for testing',
            'is_active' => true,
            'sort_order' => 10,
        ];

        $response = $this->actingAs($this->user)
            ->post('/admin/settings/configurations', $newConfigData);

        $response->assertRedirect();
        $this->assertDatabaseHas('system_configurations', $newConfigData);

        // 2. Verify configuration is available in service
        $options = $this->configService->getOptions('project_statuses');
        $this->assertArrayHasKey('new_status', $options);
        $this->assertEquals('New Status', $options['new_status']);

        // 3. Create project with new status
        Project::factory()->create([
            'developer_id' => $this->developer->id,
            'status' => 'new_status',
        ]);

        // 4. Verify project appears in reports
        $report = $this->reportService->generateMonthlyReport();
        $currentMonth = Carbon::now()->format('Y-m');
        $this->assertGreaterThan(0, $report['metrics'][$currentMonth]['new_projects']);

        // 5. Verify widgets reflect the change
        $widgetResponse = $this->actingAs($this->user)
            ->get('/admin/widgets/project-status');

        $widgetData = json_decode($widgetResponse->getContent(), true);
        $this->assertContains('New Status', $widgetData['labels']);
    }

    /** @test */
    public function cache_invalidation_works_across_entire_system()
    {
        // 1. Populate caches by accessing different parts of the system
        $this->configService->getOptions('project_statuses');
        $this->reportService->generateMonthlyReport();

        $this->actingAs($this->user)->get('/admin/widgets/project-status');
        $this->actingAs($this->user)->get('/admin/widgets/trend-analysis');

        // 2. Create new project (should trigger cache clearing)
        $project = Project::factory()->create([
            'developer_id' => $this->developer->id,
            'status' => 'on-going',
        ]);

        // 3. Verify all caches are properly invalidated
        $newReport = $this->reportService->generateMonthlyReport();
        $currentMonth = Carbon::now()->format('Y-m');

        // Report should reflect new project
        $this->assertGreaterThan(0, $newReport['metrics'][$currentMonth]['new_projects']);

        // 4. Widgets should also reflect changes
        $widgetResponse = $this->actingAs($this->user)
            ->get('/admin/widgets/project-status');

        $this->assertEquals(200, $widgetResponse->getStatus());
    }

    /** @test */
    public function theme_switching_works_across_all_components()
    {
        // 1. Set initial theme
        SystemConfiguration::factory()->create([
            'category' => 'user_preferences',
            'key' => 'theme_preference',
            'value' => 'light',
            'label' => 'Theme Preference',
        ]);

        // 2. Access different parts of the system
        $this->actingAs($this->user)->get('/admin')->assertStatus(200);
        $this->actingAs($this->user)->get('/admin/settings')->assertStatus(200);
        $this->actingAs($this->user)->get('/admin/reports/projects')->assertStatus(200);

        // 3. Change theme through settings
        $response = $this->actingAs($this->user)
            ->post('/admin/settings/theme', ['theme' => 'dark']);

        $response->assertRedirect();

        // 4. Verify theme change is persisted
        $this->assertDatabaseHas('system_configurations', [
            'category' => 'user_preferences',
            'key' => 'theme_preference',
            'value' => 'dark',
        ]);

        // 5. Verify theme is applied across all pages
        $this->actingAs($this->user)->get('/admin')->assertStatus(200);
        $this->actingAs($this->user)->get('/admin/settings')->assertStatus(200);
        $this->actingAs($this->user)->get('/admin/reports/projects')->assertStatus(200);
    }

    /** @test */
    public function dynamic_configuration_updates_affect_all_system_components()
    {
        // 1. Update existing configuration
        $config = SystemConfiguration::where('category', 'project_statuses')
            ->where('key', 'on-going')
            ->first();

        $this->configService->updateConfiguration('project_statuses', 'on-going', 'active');

        // 2. Verify change is reflected in configuration service
        $value = $this->configService->getValue('project_statuses', 'on-going');
        $this->assertEquals('active', $value);

        // 3. Create project with updated status
        $project = Project::factory()->create([
            'developer_id' => $this->developer->id,
            'status' => 'active', // Using updated value
        ]);

        // 4. Verify reports handle the change correctly
        $report = $this->reportService->generateMonthlyReport();
        $this->assertIsArray($report);

        // 5. Verify widgets adapt to the change
        $widgetResponse = $this->actingAs($this->user)
            ->get('/admin/widgets/project-status');

        $this->assertEquals(200, $widgetResponse->getStatus());
    }

    /** @test */
    public function performance_optimizations_work_under_load()
    {
        // 1. Create substantial test data
        $this->createLargeDataset();

        // 2. Measure performance of key operations
        $operations = [
            'config_retrieval' => fn() => $this->configService->getOptions('project_statuses'),
            'report_generation' => fn() => $this->reportService->generateMonthlyReport(),
            'widget_data' => fn() => $this->actingAs($this->user)->get('/admin/widgets/trend-analysis'),
        ];

        foreach ($operations as $name => $operation) {
            $start = microtime(true);
            $result = $operation();
            $executionTime = microtime(true) - $start;

            // Each operation should complete within reasonable time
            $this->assertLessThan(2.0, $executionTime, "Operation {$name} took too long: {$executionTime}s");
        }

        // 3. Verify caching improves performance
        $start = microtime(true);
        $this->reportService->generateMonthlyReport();
        $firstCallTime = microtime(true) - $start;

        $start = microtime(true);
        $this->reportService->generateMonthlyReport();
        $secondCallTime = microtime(true) - $start;

        // Second call should be significantly faster due to caching
        $this->assertLessThan($firstCallTime, $secondCallTime + 0.1);
    }

    /** @test */
    public function error_handling_is_robust_across_system()
    {
        // 1. Test handling of invalid configuration operations
        $result = $this->configService->updateConfiguration('non_existent', 'non_existent', 'value');
        $this->assertFalse($result);

        $result = $this->configService->deleteConfigurationByKey('non_existent', 'non_existent');
        $this->assertFalse($result);

        // 2. Test report generation with no data
        Project::query()->delete();
        ProjectTransaction::query()->delete();

        $report = $this->reportService->generateMonthlyReport();
        $this->assertIsArray($report);
        $this->assertEquals(0, $report['summary']['total_new_projects']);

        // 3. Test widget handling of empty data
        $widgetResponse = $this->actingAs($this->user)
            ->get('/admin/widgets/project-status');

        $this->assertEquals(200, $widgetResponse->getStatus());

        // 4. Test cache operations don't fail
        $this->cacheService->clearAllCaches();
        $this->cacheService->warmUpCaches();

        $stats = $this->cacheService->getCacheStats();
        $this->assertIsArray($stats);
    }

    /** @test */
    public function data_consistency_is_maintained_across_operations()
    {
        // 1. Create project and transaction
        $project = Project::factory()->create([
            'developer_id' => $this->developer->id,
            'status' => 'on-going',
        ]);

        $transaction = ProjectTransaction::factory()->create([
            'project_id' => $project->id,
            'type' => 'investment',
            'amount' => 100000,
        ]);

        // 2. Generate initial report
        $initialReport = $this->reportService->generateMonthlyReport();
        $currentMonth = Carbon::now()->format('Y-m');
        $initialInvestment = $initialReport['metrics'][$currentMonth]['total_investment'];

        // 3. Update transaction amount
        $transaction->update(['amount' => 150000]);

        // 4. Verify report reflects the change
        $updatedReport = $this->reportService->generateMonthlyReport();
        $updatedInvestment = $updatedReport['metrics'][$currentMonth]['total_investment'];

        $this->assertGreaterThan($initialInvestment, $updatedInvestment);
        $this->assertEquals(50000, $updatedInvestment - $initialInvestment);

        // 5. Verify widgets also reflect the change
        $widgetResponse = $this->actingAs($this->user)
            ->get('/admin/widgets/transaction-summary');

        $widgetData = json_decode($widgetResponse->getContent(), true);
        $this->assertGreaterThanOrEqual(150000, $widgetData['total_investment']);
    }

    /** @test */
    public function user_permissions_are_respected_throughout_system()
    {
        // Test that authenticated users can access all features
        $this->actingAs($this->user)
            ->get('/admin')
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->get('/admin/settings')
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->get('/admin/reports/projects')
            ->assertStatus(200);

        // Test that unauthenticated users are redirected
        $this->get('/admin')
            ->assertRedirect('/admin/login');

        $this->get('/admin/settings')
            ->assertRedirect('/admin/login');

        $this->get('/admin/reports/projects')
            ->assertRedirect('/admin/login');
    }

    /** @test */
    public function system_handles_concurrent_operations_correctly()
    {
        // Simulate concurrent configuration updates
        $config = SystemConfiguration::factory()->create([
            'category' => 'test_concurrent',
            'key' => 'test_key',
            'value' => 'original_value',
        ]);

        // Multiple updates should not cause conflicts
        for ($i = 0; $i < 5; $i++) {
            $result = $this->configService->updateConfiguration(
                'test_concurrent',
                'test_key',
                "updated_value_{$i}"
            );
            $this->assertTrue($result);
        }

        // Final value should be the last update
        $finalValue = $this->configService->getValue('test_concurrent', 'test_key');
        $this->assertEquals('updated_value_4', $finalValue);
    }

    /** @test */
    public function system_recovery_works_after_cache_failures()
    {
        // 1. Populate caches
        $this->configService->getOptions('project_statuses');
        $this->reportService->generateMonthlyReport();

        // 2. Simulate cache failure by clearing all caches
        Cache::flush();

        // 3. System should continue to work without caches
        $options = $this->configService->getOptions('project_statuses');
        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        $report = $this->reportService->generateMonthlyReport();
        $this->assertIsArray($report);

        // 4. Widgets should also work
        $widgetResponse = $this->actingAs($this->user)
            ->get('/admin/widgets/project-status');

        $this->assertEquals(200, $widgetResponse->getStatus());
    }

    /**
     * Create complete test data for integration testing
     */
    private function createCompleteTestData(): void
    {
        // Create developer
        $this->developer = Developer::factory()->create([
            'name' => 'Integration Test Developer',
            'is_active' => true,
        ]);

        // Create comprehensive system configurations
        $configurations = [
            // Project statuses
            ['category' => 'project_statuses', 'key' => 'on-going', 'label' => 'On Going'],
            ['category' => 'project_statuses', 'key' => 'exited', 'label' => 'Exited'],
            ['category' => 'project_statuses', 'key' => 'planning', 'label' => 'Planning'],

            // Transaction types
            ['category' => 'transaction_types', 'key' => 'investment', 'label' => 'Investment'],
            ['category' => 'transaction_types', 'key' => 'revenue', 'label' => 'Revenue'],
            ['category' => 'transaction_types', 'key' => 'expense', 'label' => 'Expense'],

            // Property types
            ['category' => 'property_types', 'key' => 'residential', 'label' => 'Residential'],
            ['category' => 'property_types', 'key' => 'commercial', 'label' => 'Commercial'],

            // Investment types
            ['category' => 'investment_types', 'key' => 'equity', 'label' => 'Equity'],
            ['category' => 'investment_types', 'key' => 'debt', 'label' => 'Debt'],
        ];

        foreach ($configurations as $config) {
            SystemConfiguration::factory()->create($config);
        }

        // Create projects across different time periods
        $statuses = ['on-going', 'exited', 'planning'];

        for ($i = 0; $i < 6; $i++) {
            $date = Carbon::now()->subMonths($i);

            Project::factory()->count(rand(2, 4))->create([
                'developer_id' => $this->developer->id,
                'status' => $statuses[array_rand($statuses)],
                'created_at' => $date,
            ]);
        }

        // Create transactions for projects
        $projects = Project::all();
        $transactionTypes = ['investment', 'revenue', 'expense'];

        foreach ($projects as $project) {
            foreach ($transactionTypes as $type) {
                if (rand(0, 1)) { // 50% chance for each transaction type
                    ProjectTransaction::factory()->create([
                        'project_id' => $project->id,
                        'type' => $type,
                        'amount' => rand(10000, 500000),
                        'created_at' => $project->created_at->copy()->addDays(rand(1, 30)),
                    ]);
                }
            }
        }
    }

    /**
     * Create large dataset for performance testing
     */
    private function createLargeDataset(): void
    {
        // Create many projects
        Project::factory()->count(100)->create([
            'developer_id' => $this->developer->id,
            'status' => 'on-going',
            'created_at' => Carbon::now()->subMonths(rand(0, 11)),
        ]);

        // Create many transactions
        $projects = Project::all();
        foreach ($projects as $project) {
            ProjectTransaction::factory()->count(rand(1, 5))->create([
                'project_id' => $project->id,
                'created_at' => $project->created_at->copy()->addDays(rand(1, 30)),
            ]);
        }
    }
}
