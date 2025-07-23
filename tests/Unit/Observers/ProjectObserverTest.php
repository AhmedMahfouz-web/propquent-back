<?php

namespace Tests\Unit\Observers;

use App\Models\Project;
use App\Observers\ProjectObserver;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectObserverTest extends TestCase
{
    use RefreshDatabase;

    protected CacheService $cacheService;
    protected ProjectObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = $this->mock(CacheService::class);
        $this->observer = new ProjectObserver($this->cacheService);
    }

    /** @test */
    public function it_clears_cache_when_project_is_created()
    {
        // Arrange
        $this->cacheService->shouldReceive('clearProjectCaches')->once();

        // Act
        $project = Project::factory()->create();

        // Assert - Expectation is verified automatically by mock
    }

    /** @test */
    public function it_clears_cache_when_project_is_updated()
    {
        // Arrange
        $project = Project::factory()->create();
        $this->cacheService->shouldReceive('clearProjectCaches')->once();

        // Act
        $project->update(['name' => 'Updated Name']);

        // Assert - Expectation is verified automatically by mock
    }

    /** @test */
    public function it_clears_cache_when_project_is_deleted()
    {
        // Arrange
        $project = Project::factory()->create();
        $this->cacheService->shouldReceive('clearProjectCaches')->once();

        // Act
        $project->delete();

        // Assert - Expectation is verified automatically by mock
    }

    /** @test */
    public function it_clears_cache_when_project_is_restored()
    {
        // Arrange
        $project = Project::factory()->create();
        $project->delete();
        $this->cacheService->shouldReceive('clearProjectCaches')->once();

        // Act
        $project->restore();

        // Assert - Expectation is verified automatically by mock
    }

    /** @test */
    public function it_clears_cache_when_project_is_force_deleted()
    {
        // Arrange
        $project = Project::factory()->create();
        $this->cacheService->shouldReceive('clearProjectCaches')->once();

        // Act
        $project->forceDelete();

        // Assert - Expectation is verified automatically by mock
    }

    /** @test */
    public function observer_methods_call_clear_related_caches()
    {
        // Arrange
        $project = Project::factory()->make();

        // Act & Assert
        $this->cacheService->shouldReceive('clearProjectCaches')->times(5);

        $this->observer->created($project);
        $this->observer->updated($project);
        $this->observer->deleted($project);
        $this->observer->restored($project);
        $this->observer->forceDeleted($project);
    }
}
