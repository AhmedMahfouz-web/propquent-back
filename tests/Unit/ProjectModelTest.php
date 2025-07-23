<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Developer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_generates_uuid_on_creation()
    {
        $developer = Developer::factory()->create();
        
        $project = Project::create([
            'title' => 'Test Project',
            'developer_id' => $developer->id,
        ]);

        $this->assertTrue(Str::isUuid($project->id));
        $this->assertEquals($project->id, $project->key);
    }

    public function test_project_belongs_to_developer()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);

        $this->assertInstanceOf(Developer::class, $project->developer);
        $this->assertEquals($developer->id, $project->developer->id);
    }

    public function test_project_has_media_collections()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);

        $this->assertTrue(method_exists($project, 'registerMediaCollections'));
        $this->assertTrue(method_exists($project, 'registerMediaConversions'));
    }

    public function test_project_casts_attributes_correctly()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create([
            'developer_id' => $developer->id,
            'area' => '150.50',
            'bedrooms' => '3',
            'bathrooms' => '2',
            'entry_date' => '2024-01-01',
            'exit_date' => '2025-01-01',
        ]);

        $this->assertEquals('150.50', $project->area);
        $this->assertIsInt($project->bedrooms);
        $this->assertIsInt($project->bathrooms);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->entry_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->exit_date);
    }
}
