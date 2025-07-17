<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get pagination parameters with defaults
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);
        
        // Validate per_page to prevent abuse
        $perPage = min(max($perPage, 1), 100);
        
        // Use pagination and selective eager loading
        $projects = Project::with([
            'developer:id,name,email', // Only load specific fields
            'statusChanges' => function($query) {
                $query->latest()->limit(5); // Only latest 5 status changes
            },
            'transactions' => function($query) {
                $query->latest()->limit(10); // Only latest 10 transactions
            }
        ])
        ->select('id', 'key', 'title', 'developer_id', 'location', 'type', 'status', 'created_at', 'updated_at')
        ->paginate($perPage);
        
        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::with([
            'developer:id,name,email,phone', // Only necessary developer fields
            'statusChanges' => function($query) {
                $query->latest()->limit(20); // Limit status changes
            },
            'transactions' => function($query) {
                $query->latest()->limit(50); // Limit transactions
            }
        ])->findOrFail($id);
        
        // Load images separately with pagination to avoid memory issues
        $images = $project->getMedia('images')
            ->take(10) // Limit to 10 images to prevent memory exhaustion
            ->map(function($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'name' => $media->name,
                    'size' => $media->size,
                ];
            });
        
        $projectData = $project->toArray();
        $projectData['images'] = $images;
        
        return response()->json($projectData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
