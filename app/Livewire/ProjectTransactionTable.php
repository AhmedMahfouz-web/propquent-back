<?php

namespace App\Livewire;

use App\Models\ProjectTransaction;
use App\Models\Project;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use Filament\Notifications\Notification;

class ProjectTransactionTable extends Component
{
    public $transactions = [];
    public $draftRows = [];
    public $projects = [];
    public $financialTypes = [];
    public $servingTypes = [];
    public $transactionMethods = [];
    public $statuses = [];
    
    // Sorting properties
    public $sortField = 'transaction_date';
    public $sortDirection = 'desc';
    public $isLoading = false;
    
    // Filter properties
    public $filters = [
        'project' => '',
        'financial_type' => '',
        'serving' => '',
        'amount_min' => '',
        'amount_max' => '',
        'method' => '',
        'reference_no' => '',
        'status' => '',
        'transaction_date_from' => '',
        'transaction_date_to' => '',
        'due_date_from' => '',
        'due_date_to' => '',
        'actual_date_from' => '',
        'actual_date_to' => '',
        'note' => '',
    ];

    public function mount()
    {
        $this->loadData();
        $this->loadOptions();
    }

    public function loadData()
    {
        $query = ProjectTransaction::with('project.developer');
        
        // Apply filters
        $this->applyFilters($query);
        
        // Apply sorting
        if ($this->sortField === 'project') {
            $query->join('projects', 'project_transactions.project_key', '=', 'projects.key')
                  ->orderBy('projects.title', $this->sortDirection)
                  ->select('project_transactions.*');
        } elseif ($this->sortField === 'developer') {
            $query->join('projects', 'project_transactions.project_key', '=', 'projects.key')
                  ->join('developers', 'projects.developer_id', '=', 'developers.id')
                  ->orderBy('developers.name', $this->sortDirection)
                  ->select('project_transactions.*');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        
        $this->transactions = $query->get()->toArray();
    }

    public function loadOptions()
    {
        $this->projects = Project::with('developer')
            ->get()
            ->mapWithKeys(function ($project) {
                return [$project->key => "{$project->title} ({$project->developer->name})"];
            })
            ->toArray();

        $this->financialTypes = ProjectTransaction::getAvailableFinancialTypes();
        $this->servingTypes = ProjectTransaction::getAvailableServingTypes();
        $this->transactionMethods = ProjectTransaction::getAvailableTransactionMethods();
        $this->statuses = ProjectTransaction::getAvailableStatuses();
    }

    public function addNewRow()
    {
        $newRowId = 'draft_' . uniqid();
        $this->draftRows[$newRowId] = [
            'id' => $newRowId,
            'project_key' => '',
            'financial_type' => '',
            'serving' => '',
            'amount' => '',
            'method' => '',
            'reference_no' => '',
            'status' => '',
            'transaction_date' => now()->format('Y-m-d'),
            'due_date' => '',
            'actual_date' => '',
            'note' => '',
            'is_draft' => true,
        ];
    }

    public function updateDraftRow($rowId, $field, $value)
    {
        if (isset($this->draftRows[$rowId])) {
            // Convert empty date strings to null
            if (in_array($field, ['transaction_date', 'due_date', 'actual_date']) && empty($value)) {
                $value = null;
            }
            
            $this->draftRows[$rowId][$field] = $value;

            // Check if all required fields are filled and attempt to save
            $this->attemptSaveDraftRow($rowId);
        }
    }

    public function updateExistingRow($transactionId, $field, $value)
    {
        try {
            $transaction = ProjectTransaction::find($transactionId);
            if ($transaction) {
                // Convert empty date strings to null
                if (in_array($field, ['transaction_date', 'due_date', 'actual_date']) && empty($value)) {
                    $value = null;
                }
                
                // Validate the single field update
                $validator = Validator::make([$field => $value], [
                    $field => $this->getFieldValidationRule($field)
                ]);

                if ($validator->fails()) {
                    Notification::make()
                        ->title('Validation Error')
                        ->body($validator->errors()->first())
                        ->danger()
                        ->send();
                    return;
                }

                $transaction->update([$field => $value]);
                $this->loadData(); // Refresh data

                Notification::make()
                    ->title('Saved')
                    ->body('Changes saved automatically')
                    ->success()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to save: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function getFieldValidationRule($field)
    {
        $rules = [
            'project_key' => 'required|exists:projects,key',
            'financial_type' => 'required|in:' . implode(',', array_keys($this->financialTypes)),
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'status' => 'required|in:' . implode(',', array_keys($this->statuses)),
            'serving' => 'nullable|in:' . implode(',', array_keys($this->servingTypes)),
            'method' => 'nullable|in:' . implode(',', array_keys($this->transactionMethods)),
            'reference_no' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'actual_date' => 'nullable|date',
            'note' => 'nullable|string|max:65535',
        ];

        return $rules[$field] ?? 'nullable';
    }

    public function getValidationErrors($row)
    {
        $errors = [];
        $requiredFields = ['project_key', 'financial_type', 'amount', 'transaction_date', 'status'];

        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                $errors[] = $field;
            }
        }

        return $errors;
    }

    private function attemptSaveDraftRow($rowId)
    {
        $row = $this->draftRows[$rowId];

        // Check if required fields are filled
        $requiredFields = ['project_key', 'financial_type', 'amount', 'transaction_date', 'status'];
        $hasAllRequired = true;

        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                $hasAllRequired = false;
                break;
            }
        }

        if ($hasAllRequired) {
            // Convert empty date strings to null before validation and saving
            foreach (['transaction_date', 'due_date', 'actual_date'] as $dateField) {
                if (isset($row[$dateField]) && empty($row[$dateField])) {
                    $row[$dateField] = null;
                }
            }
            
            // Validate the data
            $validator = Validator::make($row, ProjectTransaction::getValidationRules());

            if (!$validator->fails()) {
                try {
                    // Remove draft-specific fields
                    unset($row['id'], $row['is_draft']);

                    // Create the transaction
                    ProjectTransaction::create($row);

                    // Remove from draft rows
                    unset($this->draftRows[$rowId]);

                    // Reload data
                    $this->loadData();

                    Notification::make()
                        ->title('Saved')
                        ->body('Transaction saved to database')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Error')
                        ->body('Failed to save transaction: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            }
        }
    }

    public function deleteDraftRow($rowId)
    {
        unset($this->draftRows[$rowId]);
    }

    public function deleteTransaction($transactionId)
    {
        $transaction = ProjectTransaction::find($transactionId);
        if ($transaction) {
            $transaction->delete();
            $this->loadData();

            Notification::make()
                ->title('Deleted')
                ->body('Transaction deleted successfully')
                ->success()
                ->send();
        }
    }

    private function applyFilters($query)
    {
        // Project filter
        if (!empty($this->filters['project'])) {
            $query->whereHas('project', function ($q) {
                $q->where('title', 'like', '%' . $this->filters['project'] . '%')
                  ->orWhere('key', 'like', '%' . $this->filters['project'] . '%');
            });
        }
        
        // Financial type filter
        if (!empty($this->filters['financial_type'])) {
            $query->where('financial_type', $this->filters['financial_type']);
        }
        
        // Serving filter
        if (!empty($this->filters['serving'])) {
            $query->where('serving', $this->filters['serving']);
        }
        
        // Amount range filter
        if (!empty($this->filters['amount_min'])) {
            $query->where('amount', '>=', $this->filters['amount_min']);
        }
        if (!empty($this->filters['amount_max'])) {
            $query->where('amount', '<=', $this->filters['amount_max']);
        }
        
        // Method filter
        if (!empty($this->filters['method'])) {
            $query->where('method', $this->filters['method']);
        }
        
        // Reference filter
        if (!empty($this->filters['reference_no'])) {
            $query->where('reference_no', 'like', '%' . $this->filters['reference_no'] . '%');
        }
        
        // Status filter
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        
        // Transaction date range filter
        if (!empty($this->filters['transaction_date_from'])) {
            $query->where('transaction_date', '>=', $this->filters['transaction_date_from']);
        }
        if (!empty($this->filters['transaction_date_to'])) {
            $query->where('transaction_date', '<=', $this->filters['transaction_date_to']);
        }
        
        // Due date range filter
        if (!empty($this->filters['due_date_from'])) {
            $query->where('due_date', '>=', $this->filters['due_date_from']);
        }
        if (!empty($this->filters['due_date_to'])) {
            $query->where('due_date', '<=', $this->filters['due_date_to']);
        }
        
        // Actual date range filter
        if (!empty($this->filters['actual_date_from'])) {
            $query->where('actual_date', '>=', $this->filters['actual_date_from']);
        }
        if (!empty($this->filters['actual_date_to'])) {
            $query->where('actual_date', '<=', $this->filters['actual_date_to']);
        }
        
        // Note filter
        if (!empty($this->filters['note'])) {
            $query->where('note', 'like', '%' . $this->filters['note'] . '%');
        }
    }
    
    public function updatedFilters()
    {
        $this->loadData();
    }
    
    public function resetFilters()
    {
        $this->filters = [
            'project' => '',
            'financial_type' => '',
            'serving' => '',
            'amount_min' => '',
            'amount_max' => '',
            'method' => '',
            'reference_no' => '',
            'status' => '',
            'transaction_date_from' => '',
            'transaction_date_to' => '',
            'due_date_from' => '',
            'due_date_to' => '',
            'actual_date_from' => '',
            'actual_date_to' => '',
            'note' => '',
        ];
        $this->loadData();
    }
    
    public function sortBy($field)
    {
        $this->isLoading = true;
        
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->loadData();
        $this->isLoading = false;
    }
    
    public function render()
    {
        return view('livewire.project-transaction-table');
    }
}
