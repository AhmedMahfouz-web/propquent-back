<?php

namespace Tests\Feature;

use App\Filament\Pages\ProjectFinancialReport;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectFinancialReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_status_and_updates_correctly()
    {
        // Arrange: Create projects with different statuses
        Project::factory()->create(['status' => 'active', 'project_key' => 'PROJ-001']);
        Project::factory()->create(['status' => 'inactive', 'project_key' => 'PROJ-002']);

        // Act & Assert: Initial load shows all projects
        Livewire::test(ProjectFinancialReport::class)
            ->assertSee('PROJ-001')
            ->assertSee('PROJ-002');

        // Act & Assert: Filter by 'active' status
        Livewire::test(ProjectFinancialReport::class)
            ->set('status', 'active')
            ->assertSee('PROJ-001')
            ->assertDontSee('PROJ-002');

        // Act & Assert: Change filter to 'inactive' status
        Livewire::test(ProjectFinancialReport::class)
            ->set('status', 'inactive')
            ->assertSee('PROJ-002')
            ->assertDontSee('PROJ-001');

        // Act & Assert: Clear the filter
        Livewire::test(ProjectFinancialReport::class)
            ->set('status', '')
            ->assertSee('PROJ-001')
            ->assertSee('PROJ-002');
    }
}
