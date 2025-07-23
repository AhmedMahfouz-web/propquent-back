<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\Developer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_can_add_media()
    {
        Storage::fake('public');
        
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        
        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600);
        
        $media = $project->addMedia($file)
            ->usingFileName('project-image.jpg')
            ->toMediaCollection('images');
        
        $this->assertNotNull($media);
        $this->assertEquals('project-image.jpg', $media->file_name);
        $this->assertEquals('images', $media->collection_name);
    }

    public function test_project_has_media_collections()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        
        $this->assertTrue(method_exists($project, 'registerMediaCollections'));
        $this->assertTrue(method_exists($project, 'registerMediaConversions'));
    }

    public function test_project_implements_has_media_interface()
    {
        $developer = Developer::factory()->create();
        $project = Project::factory()->create(['developer_id' => $developer->id]);
        
        $this->assertInstanceOf(\Spatie\MediaLibrary\HasMedia::class, $project);
    }

    public function test_media_library_configuration_exists()
    {
        $this->assertTrue(config()->has('media-library'));
        $this->assertIsArray(config('media-library'));
    }
}
