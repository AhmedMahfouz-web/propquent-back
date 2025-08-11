<?php

namespace App\Filament\Resources\ProjectEvaluationResource\Pages;

use App\Filament\Resources\ProjectEvaluationResource;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class BulkProjectEvaluations extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ProjectEvaluationResource::class;
    protected static string $view = 'filament.resources.project-evaluation-resource.pages.bulk-project-evaluations';
    protected static ?string $title = 'Bulk Edit Project Evaluations';

    public array $evaluations = [];
    public array $availableMonths = [];
    public $projects;

    public function mount(): void
    {
        // Get all projects
        $this->projects = Project::orderBy('title')->get();

        // Generate available months (last 12 months + next 6 months)
        $monthsCollection = collect();
        for ($i = -12; $i <= 6; $i++) {
            $date = Carbon::now()->addMonths($i)->startOfMonth();
            $monthsCollection->push($date->format('Y-m'));
        }
        $this->availableMonths = $monthsCollection->toArray();

        // Load existing evaluations
        $this->loadEvaluations();
    }

    public function loadEvaluations(): void
    {
        $existingEvaluations = ProjectEvaluation::whereIn('project_key', $this->projects->pluck('key'))
            ->get()
            ->groupBy('project_key')
            ->map(function ($evaluations) {
                return $evaluations->keyBy(function ($evaluation) {
                    return $evaluation->evaluation_date->format('Y-m');
                });
            });

        foreach ($this->projects as $project) {
            foreach ($this->availableMonths as $month) {
                $key = $project->key . '_' . $month;
                $evaluation = $existingEvaluations->get($project->key)?->get($month);
                $this->evaluations[$key] = $evaluation ? $evaluation->evaluation_amount : 0;
            }
        }
    }

    public function save(): void
    {
        foreach ($this->evaluations as $key => $amount) {
            [$projectKey, $month] = explode('_', $key, 2);

            if ($amount > 0) {
                ProjectEvaluation::setEvaluationForMonth($projectKey, $month, $amount);
            } else {
                // Remove evaluation if amount is 0
                ProjectEvaluation::where('project_key', $projectKey)
                    ->where('evaluation_date', $month . '-01')
                    ->delete();
            }
        }

        Notification::make()
            ->title('Evaluations Updated')
            ->body('All project evaluations have been updated successfully.')
            ->success()
            ->send();

        // Dispatch event to refresh any listening financial reports
        $this->dispatch('evaluation-updated');
    }

    public function copyFromPreviousMonth(string $projectKey): void
    {
        $months = $this->availableMonths;

        for ($i = 1; $i < count($months); $i++) {
            $currentMonth = $months[$i];
            $previousMonth = $months[$i - 1];

            $previousKey = $projectKey . '_' . $previousMonth;
            $currentKey = $projectKey . '_' . $currentMonth;

            if (isset($this->evaluations[$previousKey]) && $this->evaluations[$previousKey] > 0) {
                $this->evaluations[$currentKey] = $this->evaluations[$previousKey];
            }
        }

        Notification::make()
            ->title('Values Copied')
            ->body('Previous month values have been copied forward.')
            ->success()
            ->send();
    }

    public function clearProject(string $projectKey): void
    {
        foreach ($this->availableMonths as $month) {
            $key = $projectKey . '_' . $month;
            $this->evaluations[$key] = 0;
        }

        Notification::make()
            ->title('Project Cleared')
            ->body('All evaluations for this project have been cleared.')
            ->success()
            ->send();
    }
}
