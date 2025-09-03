<?php

namespace App\Filament\Resources\ValueCorrectionResource\Pages;

use App\Filament\Resources\ValueCorrectionResource;
use App\Models\Project;
use App\Models\ValueCorrection;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class BulkValueCorrections extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ValueCorrectionResource::class;
    protected static string $view = 'filament.resources.value-correction-resource.pages.bulk-value-corrections';
    protected static ?string $title = 'Bulk Edit Value Corrections';

    public array $corrections = [];
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

        // Load existing corrections
        $this->loadCorrections();
    }

    public function loadCorrections(): void
    {
        $existingCorrections = ValueCorrection::whereIn('project_key', $this->projects->pluck('key'))
            ->get()
            ->groupBy('project_key')
            ->map(function ($corrections) {
                return $corrections->keyBy(function ($correction) {
                    return $correction->correction_date->format('Y-m');
                });
            });

        foreach ($this->projects as $project) {
            foreach ($this->availableMonths as $month) {
                $key = $project->key . '_' . $month;
                $correction = $existingCorrections->get($project->key)?->get($month);
                $this->corrections[$key] = $correction ? $correction->correction_amount : 0;
            }
        }
    }

    public function save(): void
    {
        foreach ($this->corrections as $key => $amount) {
            [$projectKey, $month] = explode('_', $key, 2);

            if ($amount > 0) {
                ValueCorrection::setCorrectionForMonth($projectKey, $month, $amount);
            } else {
                // Remove correction if amount is 0
                ValueCorrection::where('project_key', $projectKey)
                    ->where('correction_date', $month . '-01')
                    ->delete();
            }
        }

        Notification::make()
            ->title('Value Corrections Updated')
            ->body('All project value corrections have been updated successfully.')
            ->success()
            ->send();

        // Dispatch event to refresh any listening financial reports
        $this->dispatch('correction-updated');
    }

    public function copyFromPreviousMonth(string $projectKey): void
    {
        $months = $this->availableMonths;

        for ($i = 1; $i < count($months); $i++) {
            $currentMonth = $months[$i];
            $previousMonth = $months[$i - 1];

            $previousKey = $projectKey . '_' . $previousMonth;
            $currentKey = $projectKey . '_' . $currentMonth;

            if (isset($this->corrections[$previousKey]) && $this->corrections[$previousKey] > 0) {
                $this->corrections[$currentKey] = $this->corrections[$previousKey];
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
            $this->corrections[$key] = 0;
        }

        Notification::make()
            ->title('Project Cleared')
            ->body('All value corrections for this project have been cleared.')
            ->success()
            ->send();
    }
}
