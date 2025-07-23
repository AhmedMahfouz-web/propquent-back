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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CompleteSystemValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();

        // Clear cache before each test
        Cache::flush();

        // Run seeders to create realistic data
        $this->seed();
    }

    /** @test */
    public function all_system_requirements_are_met()
    {
        // Requirement 1.1: Dynamic project status management
        $this->validateDynamicProjectStatusManagement();

        // Requirement 1.2: Configuration CRUD operations
        $this->validateConfigurationCrudOperations();

        // Requirement 2.1-2.3: Dynamic transaction and property types
        $this->validateDynamicTransactionAndPropertyTypes();

        // Requirement 3.1-3.6: Monthly project reports
        $this->validateMonthlyProjectReports();

        // Requirement 4.1-4.5: Theme switcher functionality
        $this->validateThemeSwitcherFunctionality();

        // Requirement 5.1-5.5: Dynamic dashboard widgets
        $this->validateDynamicDashboardWidgets();

        // Requirement 6.1-6.6: Realistic seed data
        $this->validateRealisticSeedData();

        // Requirement 7.1-7.6: Centralized settings page
        $this->validateCentralizedSettingsPage();
    }

    /** @test */
    public function system_performance_meets_requirements()
    {
        // Test configuration retrieval performance
        $start = microtime(true);
        $configService = app(ConfigurationService::class);
        $options = $configService->getOptions('project_statuses');
        $configTime = microtime(true) - $start;

        $this->assertLessThan(0.1, $configTime, 'Configuration retrieval should be fast');
        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        // Test report generation performance
        $start = microtime(true);
        $reportService = app(ProjectReportService::class);
        $report = $reportService->generateMonthlyReport();
        $reportTime = microtime(true) - $start;

        $this->assertLessThan(2.0, $reportTime, 'Report generation should complete within 2 seconds');
        $this->assertIsArray($report);

        // Test widget performance
        $start = microtime(true);
        $response = $this->actingAs($this->user)->get('/admin/widgets/trend-analysis');
        $widgetTime = microtime(true) - $start;

        $this->assertLessThan(1.0, $widgetTime, 'Widget loading should be fast');
        $response->assertStatus(200);

        // Test caching effectiveness
        $start = microtime(true);
        $report1 = $reportService->generateMonthlyReport();
        $firstCallTime = microtime(true) - $start;

        $start = microtime(true);
        $report2 = $reportService->generateMonthlyReport();
        $secondCallTime = microtime(true) - $start;

        $this->assertEquals($report1, $report2);
        $this->assertLessThan($firstCallTime, $secondCallTime + 0.01, 'Cached calls should be faster');
    }

    /** @test */
    public function system_handles_all_edge_cases()
    {
        // Test empty data scenarios
        Project::query()->delete();
        ProjectTransaction::query()->delete();

        $reportService = app(ProjectReportService::class);
        $report = $reportService->generateMonthlyReport();

        $this->assertIsArray($report);
        $this->assertEquals(0, $report['summary']['total_new_projects']);

        // Test invalid configuration operations
        $configService = app(ConfigurationService::class);

        $result = $configService->getValue('non_existent', 'non_existent', 'default');
        $this->assertEquals('default', $result);

        $result = $configService->updateConfiguration('non_existent', 'non_existent', 'value');
        $this->assertFalse($result);

        // Test widget handling of empty data
        $response = $this->actingAs($this->user)->get('/admin/widgets/project-status');
        $response->assertStatus(200);

        // Test cache operations with no data
        $cacheService = app(CacheService::class);
        $cacheService->clearAllCaches();
        $warmed = $cacheService->warmUpCaches();
        $this->assertIsArray($warmed);
    }

    /** @test */
    public function all_artisan_commands_work_correctly()
    {
        // Test cache management command
        Artisan::call('cache:manage', ['action' => 'clear-all']);
        $this->assertEquals(0, Artisan::output());

        Artisan::call('cache:manage', ['action' => 'warm']);
        $this->assertEquals(0, Artisan::output());

        Artisan::call('cache:manage', ['action' => 'stats']);
        $this->assertEquals(0, Artisan::output());

        // Test specific cache clearing
        Artisan::call('cache:manage', ['action' => 'clear', '--type' => 'projects']);
        $this->assertEquals(0, Artisan::output());

        // Test error handling
        Artisan::call('cache:manage', ['action' => 'invalid']);
        $this->assertEquals(1, Artisan::output());
    }

    /** @test */
    public function data_integrity_is_maintained()
    {
        // Create project and verify it appears in all relevant places
        $developer = Developer::first();
        $project = Project::factory()->create([
            'developer_id' => $developer->id,
            'status' => 'on-going',
            'name' => 'Data Integrity Test Project',
        ]);

        // Should appear in configuration options
        $configService = app(ConfigurationService::class);
        $statuses = $configService->getOptions('project_statuses');
        $this->assertArrayHasKey('on-going', $statuses);

        // Should appear in reports
        $reportService = app(ProjectReportService::class);
        $report = $reportService->generateMonthlyReport();
        $currentMonth = Carbon::now()->format('Y-m');
        $this->assertGreaterThan(0, $report['metrics'][$currentMonth]['new_projects']);

        // Should appear in widgets
        $response = $this->actingAs($this->user)->get('/admin/widgets/project-status');
        $widgetData = json_decode($response->getContent(), true);
        $this->assertContains('On Going', $widgetData['labels']);

        // Create transaction and verify consistency
        $transaction = ProjectTransaction::factory()->create([
            'project_id' => $project->id,
            'type' => 'investment',
            'amount' => 100000,
        ]);

        // Should appear in financial reports
        $report = $reportService->generateMonthlyReport();
        $this->assertGreaterThan(0, $report['metrics'][$currentMonth]['total_investment']);

        // Should appear in transaction widgets
        $response = $this->actingAs($this->user)->get('/admin/widgets/transaction-summary');
        $transactionData = json_decode($response->getContent(), true);
        $this->assertGreaterThanOrEqual(100000, $transactionData['total_investment']);
    }

    /** @test */
    public function user_experience_flows_work_seamlessly()
    {
        // Test complete user workflow

        // 1. User logs in and accesses dashboard
        $response = $this->actingAs($this->user)->get('/admin');
        $response->assertStatus(200)
            ->assertSee('Project Status Distribution')
            ->assertSee('Transaction Summary');

        // 2. User accesses settings and creates new configuration
        $response = $this->actingAs($this->user)->get('/admin/settings');
        $response->assertStatus(200);

        $newConfigData = [
            'category' => 'project_statuses',
            'key' => 'user_test_status',
            'value' => 'user_test_status',
            'label' => 'User Test Status',
            'is_active' => true,
            'sort_order' => 99,
        ];

        $response = $this->actingAs($this->user)
            ->post('/admin/settings/configurations', $newConfigData);
        $response->assertRedirect();

        // 3. User switches theme
        $response = $this->actingAs($this->user)
            ->post('/admin/settings/theme', ['theme' => 'dark']);
        $response->assertRedirect();

        // 4. User accesses reports
        $response = $this->actingAs($this->user)->get('/admin/reports/projects');
        $response->assertStatus(200)
            ->assertSee('Monthly Report');

        // 5. User exports report
        $response = $this->actingAs($this->user)
            ->get('/admin/reports/projects/export?format=csv');
        $response->assertStatus(200);

        // 6. User accesses individual widgets
        $widgets = ['project-status', 'transaction-summary', 'trend-analysis', 'recent-activity'];

        foreach ($widgets as $widget) {
            $response = $this->actingAs($this->user)->get("/admin/widgets/{$widget}");
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function system_security_requirements_are_met()
    {
        // Test authentication requirements
        $protectedRoutes = [
            '/admin',
            '/admin/settings',
            '/admin/reports/projects',
            '/admin/widgets/project-status',
        ];

        foreach ($protectedRoutes as $route) {
            // Unauthenticated access should be denied
            $this->get($route)->assertRedirect('/admin/login');

            // Authenticated access should be allowed
            $this->actingAs($this->user)->get($route)->assertStatus(200);
        }

        // Test input validation
        $response = $this->actingAs($this->user)
            ->post('/admin/settings/configurations', [
                'category' => '',
                'key' => '',
                'label' => '',
            ]);

        $response->assertSessionHasErrors(['category', 'key', 'label']);

        // Test SQL injection prevention (basic test)
        $maliciousInput = "'; DROP TABLE system_configurations; --";

        $response = $this->actingAs($this->user)
            ->post('/admin/settings/configurations', [
                'category' => 'test',
                'key' => $maliciousInput,
                'value' => 'test',
                'label' => 'test',
            ]);

        // Should either fail validation or be safely escaped
        $this->assertTrue(
            $response->isRedirection() || $response->getStatusCode() === 422
        );
    }

    /**
     * Validate dynamic project status management
     */
    private function validateDynamicProjectStatusManagement(): void
    {
        $configService = app(ConfigurationService::class);

        // Should have project statuses
        $statuses = $configService->getOptions('project_statuses');
        $this->assertIsArray($statuses);
        $this->assertNotEmpty($statuses);

        // Should include default statuses
        $this->assertArrayHasKey('on-going', $statuses);
        $this->assertArrayHasKey('exited', $statuses);
    }

    /**
     * Validate configuration CRUD operations
     */
    private function validateConfigurationCrudOperations(): void
    {
        $configService = app(ConfigurationService::class);

        // Create
        $newConfig = $configService->createConfiguration([
            'category' => 'test_crud',
            'key' => 'test_key',
            'value' => 'test_value',
            'label' => 'Test Label',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(SystemConfiguration::class, $newConfig);

        // Read
        $value = $configService->getValue('test_crud', 'test_key');
        $this->assertEquals('test_value', $value);

        // Update
        $result = $configService->updateConfiguration('test_crud', 'test_key', 'updated_value');
        $this->assertTrue($result);

        $updatedValue = $configService->getValue('test_crud', 'test_key');
        $this->assertEquals('updated_value', $updatedValue);

        // Delete
        $result = $configService->deleteConfigurationByKey('test_crud', 'test_key');
        $this->assertTrue($result);

        $deletedValue = $configService->getValue('test_crud', 'test_key', 'default');
        $this->assertEquals('default', $deletedValue);
    }

    /**
     * Validate dynamic transaction and property types
     */
    private function validateDynamicTransactionAndPropertyTypes(): void
    {
        $configService = app(ConfigurationService::class);

        $transactionTypes = $configService->getOptions('transaction_types');
        $this->assertIsArray($transactionTypes);
        $this->assertNotEmpty($transactionTypes);

        $propertyTypes = $configService->getOptions('property_types');
        $this->assertIsArray($propertyTypes);
        $this->assertNotEmpty($propertyTypes);
    }

    /**
     * Validate monthly project reports
     */
    private function validateMonthlyProjectReports(): void
    {
        $reportService = app(ProjectReportService::class);
        $report = $reportService->generateMonthlyReport();

        $this->assertIsArray($report);
        $this->assertArrayHasKey('months', $report);
        $this->assertArrayHasKey('metrics', $report);
        $this->assertArrayHasKey('summary', $report);

        // Should have 12 months of data by default
        $this->assertCount(12, $report['months']);
    }

    /**
     * Validate theme switcher functionality
     */
    private function validateThemeSwitcherFunctionality(): void
    {
        $configService = app(ConfigurationService::class);

        // Create theme configuration
        $configService->createConfiguration([
            'category' => 'user_preferences',
            'key' => 'theme_preference',
            'value' => 'light',
            'label' => 'Theme Preference',
            'is_active' => true,
        ]);

        // Should be able to update theme
        $result = $configService->updateConfiguration('user_preferences', 'theme_preference', 'dark');
        $this->assertTrue($result);

        $theme = $configService->getValue('user_preferences', 'theme_preference');
        $this->assertEquals('dark', $theme);
    }

    /**
     * Validate dynamic dashboard widgets
     */
    private function validateDynamicDashboardWidgets(): void
    {
        $widgets = [
            'project-status',
            'transaction-summary',
            'trend-analysis',
            'recent-activity',
        ];

        foreach ($widgets as $widget) {
            $response = $this->actingAs($this->user)->get("/admin/widgets/{$widget}");
            $response->assertStatus(200);

            $data = json_decode($response->getContent(), true);
            $this->assertIsArray($data);
        }
    }

    /**
     * Validate realistic seed data
     */
    private function validateRealisticSeedData(): void
    {
        // Should have projects
        $projectCount = Project::count();
        $this->assertGreaterThan(0, $projectCount);

        // Should have transactions
        $transactionCount = ProjectTransaction::count();
        $this->assertGreaterThan(0, $transactionCount);

        // Should have developers
        $developerCount = Developer::count();
        $this->assertGreaterThan(0, $developerCount);

        // Should have configurations
        $configCount = SystemConfiguration::count();
        $this->assertGreaterThan(0, $configCount);
    }

    /**
     * Validate centralized settings page
     */
    private function validateCentralizedSettingsPage(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/settings');
        $response->assertStatus(200)
            ->assertSee('Project Statuses')
            ->assertSee('Transaction Types')
            ->assertSee('Property Types');
    }
}
