<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Developer;
use App\Services\ConfigurationService;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class ProjectStatusBreakdown extends Widget
{
    protected static string $view = 'filament.widgets.project-status-breakdown';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    public ?string $selectedStatus = null;
    public ?string $selectedDeveloper = null;
    public bool $showDetails = false;

    public function mount(): void
    {
        $this->loadData();
    }

    #[On('project-status-clicked')]
    public function handleStatusClick($status, $index): void
    {
        $this->selectedStatus = $status;
        $this->showDetails = true;
        $this->loadData();
    }

    public function selectDeveloper(?string $developerId): void
    {
        $this->selectedDeveloper = $developerId;
        $this->loadData();
    }

    public function clearFilters(): void
    {
        $this->selectedStatus = null;
        $this->selectedDeveloper = null;
        $this->showDetails = false;
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->dispatch('dataLoaded');
    }

    public function getStatusBreakdown(): array
    {
        $configService = new ConfigurationService();
        $statusOptions = $configService->getOptions('project_statuses');

        $query = Project::with('developer');

        if ($this->selectedStatus) {
            // Find the status key from the label
            $statusKey = array_search($this->selectedStatus, $statusOptions);
            if ($statusKey) {
                $query->where('status', $statusKey);
            }
        }

        if ($this->selectedDeveloper) {
            $query->where('developer_id', $this->selectedDeveloper);
        }

        $projects = $query->get();

        $breakdown = [];

        // Group by status
        $statusGroups = $projects->groupBy('status');

        foreach ($statusGroups as $status => $statusProjects) {
            $statusLabel = $statusOptions[$status] ?? ucwords(str_replace('_', ' ', $status));

            // Group by developer within each status
            $developerGroups = $statusProjects->groupBy('developer_id');
            $developers = [];

            foreach ($developerGroups as $developerId => $devProjects) {
                $developer = $devProjects->first()->developer;
                $developers[] = [
                    'id' => $developerId,
                    'name' => $developer ? $developer->name : 'Unknown Developer',
                    'count' => $devProjects->count(),
                    'projects' => $devProjects->map(function ($project) {
                        return [
                            'id' => $project->id,
                            'name' => $project->name,
                            'location' => $project->location,
                            'property_type' => $project->property_type,
                            'stage' => $project->stage,
                            'created_at' => $project->created_at->format('M d, Y'),
                            'total_area' => $project->total_area,
                        ];
                    })->toArray(),
                ];
            }

            $breakdown[] = [
                'status' => $status,
                'status_label' => $statusLabel,
                'total_count' => $statusProjects->count(),
                'developers' => $developers,
            ];
        }

        return $breakdown;
    }

    public function getDevelopers(): array
    {
        return Developer::where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getStatusColors(): array
    {
        return [
            'on-going' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            'exited' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            'planning' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            'construction' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
            'completed' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
            'paused' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
            'sold' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-400',
        ];
    }

    public function getStatusColor(string $status): string
    {
        $colors = $this->getStatusColors();
        return $colors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
    }

    public static function canView(): bool
    {
        return auth('admins')->check();
    }
}
