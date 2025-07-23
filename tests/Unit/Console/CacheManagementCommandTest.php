<?php

namespace Tests\Unit\Console;

use App\Console\Commands\CacheManagementCommand;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheManagementCommandTest extends TestCase
{
    use RefreshDatabase;

    protected CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = $this->mock(CacheService::class);
    }

    /** @test */
    public function it_can_clear_specific_cache_types()
    {
        // Arrange
        $this->cacheService->shouldReceive('clearProjectCaches')->once();

        // Act
        $this->artisan('cache:manage clear --type=projects')
            ->expectsOutput('Clearing projects caches...')
            ->expectsOutput('✅ projects caches cleared successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_clear_transaction_caches()
    {
        // Arrange
        $this->cacheService->shouldReceive('clearTransactionCaches')->once();

        // Act
        $this->artisan('cache:manage clear --type=transactions')
            ->expectsOutput('Clearing transactions caches...')
            ->expectsOutput('✅ transactions caches cleared successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_clear_configuration_caches()
    {
        // Arrange
        $this->cacheService->shouldReceive('clearConfigurationCaches')->once();

        // Act
        $this->artisan('cache:manage clear --type=configurations')
            ->expectsOutput('Clearing configurations caches...')
            ->expectsOutput('✅ configurations caches cleared successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_clear_report_caches()
    {
        // Arrange
        $this->cacheService->shouldReceive('clearReportCaches')->once();

        // Act
        $this->artisan('cache:manage clear --type=reports')
            ->expectsOutput('Clearing reports caches...')
            ->expectsOutput('✅ reports caches cleared successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_clear_widget_caches()
    {
        // Arrange
        $this->cacheService->shouldReceive('clearWidgetCaches')->once();

        // Act
        $this->artisan('cache:manage clear --type=widgets')
            ->expectsOutput('Clearing widgets caches...')
            ->expectsOutput('✅ widgets caches cleared successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_clear_all_caches()
    {
        // Arrange
        $this->cacheService->shouldReceive('clearAllCaches')->once();

        // Act
        $this->artisan('cache:manage clear-all')
            ->expectsOutput('Clearing all caches...')
            ->expectsOutput('✅ All caches cleared successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_warm_caches()
    {
        // Arrange
        $this->cacheService->shouldReceive('warmUpCaches')
            ->once()
            ->andReturn(['config_project_statuses', 'monthly_report_default']);

        // Act
        $this->artisan('cache:manage warm')
            ->expectsOutput('Warming up caches...')
            ->expectsOutput('✅ Caches warmed up successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_show_cache_stats()
    {
        // Arrange
        $stats = [
            'memory_used' => '10MB',
            'memory_peak' => '15MB',
            'total_keys' => 100,
            'cache_hits' => 500,
            'cache_misses' => 50,
            'hit_rate' => '90.91%',
        ];

        $this->cacheService->shouldReceive('getCacheStats')
            ->once()
            ->andReturn($stats);

        // Act
        $this->artisan('cache:manage stats')
            ->expectsOutput('Cache Statistics:')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_shows_error_when_cache_type_not_specified_for_clear()
    {
        // Act
        $this->artisan('cache:manage clear')
            ->expectsOutput('Please specify a cache type with --type option')
            ->expectsOutput('Available types: projects, transactions, configurations, reports, widgets')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_shows_error_for_unknown_cache_type()
    {
        // Act
        $this->artisan('cache:manage clear --type=unknown')
            ->expectsOutput('Clearing unknown caches...')
            ->expectsOutput('❌ Failed to clear unknown caches: Unknown cache type: unknown')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_shows_error_for_invalid_action()
    {
        // Act
        $this->artisan('cache:manage invalid')
            ->expectsOutput('Invalid action specified.')
            ->expectsOutput('Available actions:')
            ->expectsOutput('  clear --type=<type>  Clear specific cache type')
            ->expectsOutput('  clear-all            Clear all caches')
            ->expectsOutput('  warm                 Warm up frequently used caches')
            ->expectsOutput('  stats                Show cache statistics')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_handles_cache_service_exceptions_gracefully()
    {
        // Arrange
        $this->cacheService->shouldReceive('clearProjectCaches')
            ->once()
            ->andThrow(new \Exception('Cache service error'));

        // Act
        $this->artisan('cache:manage clear --type=projects')
            ->expectsOutput('Clearing projects caches...')
            ->expectsOutput('❌ Failed to clear projects caches: Cache service error')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_handles_warm_cache_exceptions_gracefully()
    {
        // Arrange
        $this->cacheService->shouldReceive('warmUpCaches')
            ->once()
            ->andThrow(new \Exception('Warm cache error'));

        // Act
        $this->artisan('cache:manage warm')
            ->expectsOutput('Warming up caches...')
            ->expectsOutput('❌ Failed to warm up caches: Warm cache error')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_handles_stats_exceptions_gracefully()
    {
        // Arrange
        $this->cacheService->shouldReceive('getCacheStats')
            ->once()
            ->andReturn(['error' => 'Unable to retrieve cache statistics']);

        // Act
        $this->artisan('cache:manage stats')
            ->expectsOutput('Cache Statistics:')
            ->expectsOutput('Unable to retrieve cache statistics')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_shows_help_information()
    {
        // Act
        $this->artisan('cache:manage invalid')
            ->expectsOutput('Examples:')
            ->expectsOutput('  php artisan cache:manage clear --type=projects')
            ->expectsOutput('  php artisan cache:manage warm')
            ->expectsOutput('  php artisan cache:manage stats')
            ->assertExitCode(1);
    }
}
