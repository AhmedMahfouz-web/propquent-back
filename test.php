<?php

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
        Project::factory()->create(['status' => 'active']);
        Project::factory()->create(['status' => 'inactive']);

        // Act & Assert: Initial load shows all projects
        Livewire::test(ProjectFinancialReport::class)
            ->assertSee('active')
            ->assertSee('inactive');

        // Act & Assert: Filter by 'active' status
        Livewire::test(ProjectFinancialReport::class)
            ->set('status', 'active')
            ->assertSee('active')
            ->assertDontSee('inactive');

        // Act & Assert: Change filter to 'inactive' status
        Livewire::test(ProjectFinancialReport::class)
            ->set('status', 'inactive')
            ->assertSee('inactive')
            ->assertDontSee('active');

        // Act & Assert: Clear the filter
        Livewire::test(ProjectFinancialReport::class)
            ->set('status', '')
            ->assertSee('active')
            ->assertSee('inactive');
    }
}
