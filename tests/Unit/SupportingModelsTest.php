<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\StatusChange;
use App\Models\ProjectImage;
use App\Models\Project;
use App\Models\Developer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportingModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_model_has_correct_attributes()
    {
        $admin = Admin::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->assertEquals('Test Admin', $admin->name);
        $this->assertEquals('admin@test.com', $admin->email);
        $this->assertEquals('super_admin', $admin->role);
        $this->assertTrue($admin->is_active);
    }

    public function test_status_change_belongs_to_project_and_admin()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        $admin = Admin::factory()->create();
        
        $statusChange = StatusChange::factory()->create([
            'project_id' => $project->id,
            'changed_by' => $admin->id,
        ]);

        $this->assertInstanceOf(Project::class, $statusChange->project);
        $this->assertInstanceOf(Admin::class, $statusChange->changedBy);
        $this->assertEquals($project->id, $statusChange->project->id);
        $this->assertEquals($admin->id, $statusChange->changedBy->id);
    }

    public function test_project_image_belongs_to_project()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        
        $projectImage = ProjectImage::factory()->create([
            'project_id' => $project->id,
            'is_primary' => true,
        ]);

        $this->assertInstanceOf(Project::class, $projectImage->project);
        $this->assertEquals($project->id, $projectImage->project->id);
        $this->assertTrue($projectImage->is_primary);
    }

    public function test_admin_scope_active_works()
    {
        Admin::factory()->create(['is_active' => true]);
        Admin::factory()->create(['is_active' => false]);

        $activeAdmins = Admin::active()->get();
        
        $this->assertEquals(1, $activeAdmins->count());
        $this->assertTrue($activeAdmins->first()->is_active);
    }

    public function test_project_image_scope_primary_works()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        
        ProjectImage::factory()->create(['project_id' => $project->id, 'is_primary' => true]);
        ProjectImage::factory()->create(['project_id' => $project->id, 'is_primary' => false]);

        $primaryImages = ProjectImage::primary()->get();
        
        $this->assertEquals(1, $primaryImages->count());
        $this->assertTrue($primaryImages->first()->is_primary);
    }
}
