<?php

namespace App\Livewire;

use App\Models\ValueCorrection;
use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class QuickValueCorrectionEdit extends Component implements HasForms
{
    use InteractsWithForms;

    public string $projectKey;
    public string $month;
    public string $projectTitle;
    public ?float $correction_amount = null;
    public ?string $notes = null;
    public bool $showModal = false;
    public bool $saving = false;
    public array $data = [];

    protected $listeners = ['correction-updated' => 'refreshCorrection'];

    public function mount(string $projectKey, string $month, string $projectTitle): void
    {
        $this->projectKey = $projectKey;
        $this->month = $month;
        $this->projectTitle = $projectTitle;

        $this->loadCorrection();
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

    private function loadCorrection(): void
    {
        $this->correction_amount = ValueCorrection::getCorrectionForMonth($this->projectKey, $this->month);

        // Load notes separately using robust date parsing
        $correctionDate = $this->parseMonthToDate($this->month);
        $correction = ValueCorrection::where('project_key', $this->projectKey)
            ->whereDate('correction_date', $correctionDate)
            ->first();

        $this->notes = $correction ? $correction->notes : null;

        $this->data = [
            'correction_amount' => $this->correction_amount,
            'notes' => $this->notes,
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('correction_amount')
                    ->label('Correction Amount')
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

            ValueCorrection::setCorrectionForMonth(
                $this->projectKey,
                $this->month,
                $data['correction_amount'],
                $data['notes'] ?? null
            );

            // Reload the correction to ensure we have the latest data
            $this->loadCorrection();

            Notification::make()
                ->title('Value Correction Updated')
                ->body("Value correction for {$this->projectTitle} in " . date('M Y', strtotime($this->month)) . " has been updated.")
                ->success()
                ->send();

            $this->closeModal();
            // Dispatch to refresh other correction components
            $this->dispatch('correction-updated', projectKey: $this->projectKey, month: $this->month);

            // Dispatch globally to refresh the entire report
            $this->dispatch('correction-updated', projectKey: $this->projectKey, month: $this->month);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to update value correction: ' . $e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->saving = false;
        }
    }

    public function refreshCorrection($projectKey = null, $month = null): void
    {
        if ($projectKey === $this->projectKey && $month === $this->month) {
            $this->loadCorrection();
        }
    }

    public function render()
    {
        return view('livewire.quick-value-correction-edit');
    }
}
