<?php

namespace App\Livewire;

use App\Models\ProjectEvaluation;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class QuickEvaluationEdit extends Component implements HasForms
{
    use InteractsWithForms;

    public string $projectKey;
    public string $month;
    public string $projectTitle;
    public ?float $evaluation_amount = null;
    public ?string $notes = null;
    public bool $showModal = false;
    public bool $saving = false;
    public array $data = [];

    protected $listeners = ['evaluation-updated' => 'refreshEvaluation'];

    public function mount(string $projectKey, string $month, string $projectTitle): void
    {
        $this->projectKey = $projectKey;
        $this->month = $month;
        $this->projectTitle = $projectTitle;

        $this->loadEvaluation();
    }

    private function parseMonthToDate(string $month): string
    {
        // Handle different month formats
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            // Format: 2025-07
            return $month . '-01';
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $month)) {
            // Format: 2025-07-01 (already full date)
            return Carbon::parse($month)->startOfMonth()->format('Y-m-d');
        } else {
            // Try to parse with Carbon
            try {
                return Carbon::parse($month)->startOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback: assume Y-m format
                return Carbon::createFromFormat('Y-m', $month)->startOfMonth()->format('Y-m-d');
            }
        }
    }

    private function loadEvaluation(): void
    {
        $this->evaluation_amount = ProjectEvaluation::getEvaluationForMonth($this->projectKey, $this->month);

        // Load notes separately using robust date parsing
        $evaluationDate = $this->parseMonthToDate($this->month);
        $evaluation = ProjectEvaluation::where('project_key', $this->projectKey)
            ->whereDate('evaluation_date', $evaluationDate)
            ->first();

        $this->notes = $evaluation ? $evaluation->notes : null;

        $this->data = [
            'evaluation_amount' => $this->evaluation_amount,
            'notes' => $this->notes,
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('evaluation_amount')
                    ->label('Evaluation Amount')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3),
            ])
            ->statePath('data');
    }

    public function openModal(): void
    {
        $this->showModal = true;
        $this->saving = false;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->saving = false;
    }

    public function save(): void
    {
        // Prevent double-clicking
        if ($this->saving) {
            return;
        }

        $this->saving = true;

        try {
            $this->form->validate();
            $data = $this->form->getState();

            ProjectEvaluation::setEvaluationForMonth(
                $this->projectKey,
                $this->month,
                $data['evaluation_amount'],
                $data['notes'] ?? null
            );

            // Reload the evaluation to ensure we have the latest data
            $this->loadEvaluation();

            Notification::make()
                ->title('Evaluation Updated')
                ->body("Evaluation for {$this->projectTitle} in " . date('M Y', strtotime($this->month)) . " has been updated.")
                ->success()
                ->send();

            $this->closeModal();
            // Dispatch to refresh other evaluation components
            $this->dispatch('evaluation-updated', projectKey: $this->projectKey, month: $this->month);

            // Dispatch to parent page to refresh the entire report
            $this->dispatch('evaluation-updated', projectKey: $this->projectKey, month: $this->month)->up();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to update evaluation: ' . $e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->saving = false;
        }
    }

    public function refreshEvaluation($projectKey = null, $month = null): void
    {
        if ($projectKey === $this->projectKey && $month === $this->month) {
            $this->loadEvaluation();
        }
    }

    public function render()
    {
        return view('livewire.quick-evaluation-edit');
    }
}
