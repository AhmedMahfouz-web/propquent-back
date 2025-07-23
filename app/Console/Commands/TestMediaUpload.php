<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Developer;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TestMediaUpload extends Command
{
    protected $signature = 'test:media-upload';
    protected $description = 'Test media upload functionality';

    public function handle()
    {
        $this->info('Testing media upload functionality...');
        
        // Create a fake image file
        $fakeImage = UploadedFile::fake()->image('test-project.jpg', 800, 600);
        
        // Get or create a project
        $developer = Developer::first();
        if (!$developer) {
            $developer = Developer::create([
                'name' => 'Test Developer',
                'email' => 'test@developer.com'
            ]);
        }
        
        $project = Project::first();
        if (!$project) {
            $project = Project::create([
                'title' => 'Test Project',
                'developer_id' => $developer->id,
                'status' => 'available',
                'stage' => 'planning'
            ]);
        }
        
        try {
            // Add media to project
            $media = $project->addMedia($fakeImage)
                ->toMediaCollection('images');
            
            $this->info('Media uploaded successfully!');
            $this->info('Media ID: ' . $media->id);
            $this->info('Media URL: ' . $media->getUrl());
            $this->info('Media Path: ' . $media->getPath());
            
            // Check if file exists
            if (Storage::disk('public')->exists($media->id . '/' . $media->file_name)) {
                $this->info('✅ File exists in storage');
            } else {
                $this->error('❌ File does not exist in storage');
            }
            
        } catch (\Exception $e) {
            $this->error('Error uploading media: ' . $e->getMessage());
        }
    }
}
