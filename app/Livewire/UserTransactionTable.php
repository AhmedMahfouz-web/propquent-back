<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserTransaction;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;

class UserTransactionTable extends Component
{
    public $transactions = [];
    public $draftRows = [];
    public $users = [];
    public $transactionTypes = [];
    public $transactionMethods = [];
    public $statuses = [];
    
    // Sorting properties
    public $sortField = 'transaction_date';
    public $sortDirection = 'desc';
    public $isLoading = false;
    
    // Filter properties
    public $filters = [
        'user' => '',
        'transaction_type' => '',
        'amount_min' => '',
        'amount_max' => '',
        'method' => '',
        'reference_no' => '',
        'status' => '',
        'transaction_date_from' => '',
        'transaction_date_to' => '',
        'actual_date_from' => '',
        'actual_date_to' => '',
        'note' => '',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $query = UserTransaction::with('user');
        
        // Apply filters
        $this->applyFilters($query);
        
        // Apply sorting
        if ($this->sortField === 'user') {
            $query->join('users', 'user_transactions.user_id', '=', 'users.id')
                  ->orderBy('users.full_name', $this->sortDirection)
                  ->select('user_transactions.*');
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }
        
        $this->transactions = $query->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'transaction_type' => $transaction->transaction_type,
                    'amount' => $transaction->amount,
                    'transaction_date' => $transaction->transaction_date?->format('Y-m-d'),
                    'actual_date' => $transaction->actual_date?->format('Y-m-d'),
                    'method' => $transaction->method,
                    'reference_no' => $transaction->reference_no,
                    'note' => $transaction->note,
                    'status' => $transaction->status,
                    'user_name' => $transaction->user->full_name ?? 'Unknown User',
                ];
            })
            ->toArray();

        $this->users = User::all()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->full_name . ' (' . $user->custom_id . ')'];
            })
            ->toArray();

        $this->transactionTypes = UserTransaction::getAvailableTransactionTypes();
        $this->transactionMethods = UserTransaction::getAvailableMethods();
        $this->statuses = UserTransaction::getAvailableStatuses();
    }

    public function addNewRow()
    {
        $newRowId = 'draft_' . uniqid();
        $this->draftRows[$newRowId] = [
            'id' => $newRowId,
            'user_id' => '',
            'transaction_type' => '',
            'amount' => '',
            'transaction_date' => today()->format('Y-m-d'),
            'actual_date' => '',
            'method' => '',
            'reference_no' => '',
            'note' => '',
            'status' => 'pending',
        ];
    }

    public function updateDraftRow($rowId, $field, $value)
    {
        if (isset($this->draftRows[$rowId])) {
            $this->draftRows[$rowId][$field] = $value;

            // Check if all required fields are filled and attempt to save
            $this->attemptSaveDraftRow($rowId);
        }
    }

    public function updateExistingRow($transactionId, $field, $value)
    {
        $transaction = UserTransaction::find($transactionId);
        if ($transaction) {
            try {
                // Validate the field
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
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Error')
                    ->body('Failed to save: ' . $e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }

    private function getFieldValidationRule($field)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'transaction_type' => 'required|in:' . implode(',', array_keys($this->transactionTypes)),
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'actual_date' => 'required|date',
            'method' => 'nullable|in:' . implode(',', array_keys($this->transactionMethods)),
            'reference_no' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', array_keys($this->statuses)),
            'note' => 'nullable|string|max:65535',
        ];

        return $rules[$field] ?? 'nullable';
    }

    private function getValidationErrors($row)
    {
        $errors = [];
        $requiredFields = ['user_id', 'transaction_type', 'amount', 'status', 'transaction_date', 'actual_date'];

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

        // Check if core fields are filled (user, type, method, amount, status, date, actual_date)
        $coreFields = ['user_id', 'transaction_type', 'amount', 'status', 'transaction_date', 'actual_date'];
        $hasCoreFields = true;

        foreach ($coreFields as $field) {
            if (empty($row[$field]) && $row[$field] !== '0') {
                $hasCoreFields = false;
                break;
            }
        }

        if ($hasCoreFields) {
            // Validate the data
            $validator = Validator::make($row, [
                'user_id' => 'required|exists:users,id',
                'transaction_type' => 'required|in:' . implode(',', array_keys($this->transactionTypes)),
                'amount' => 'required|numeric|min:0.01',
                'transaction_date' => 'required|date',
                'actual_date' => 'required|date',
                'method' => 'nullable|in:' . implode(',', array_keys($this->transactionMethods)),
                'reference_no' => 'nullable|string|max:255',
                'status' => 'required|in:' . implode(',', array_keys($this->statuses)),
                'note' => 'nullable|string|max:65535',
            ]);

            if (!$validator->fails()) {
                try {
                    // Remove draft-specific fields
                    unset($row['id']);

                    // Create the transaction
                    UserTransaction::create($row);

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
                        ->body('Failed to save: ' . $e->getMessage())
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
        $transaction = UserTransaction::find($transactionId);
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
        // User filter
        if (!empty($this->filters['user'])) {
            $query->whereHas('user', function ($q) {
                $q->where('full_name', 'like', '%' . $this->filters['user'] . '%')
                  ->orWhere('custom_id', 'like', '%' . $this->filters['user'] . '%');
            });
        }
        
        // Transaction type filter
        if (!empty($this->filters['transaction_type'])) {
            $query->where('transaction_type', $this->filters['transaction_type']);
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
            'user' => '',
            'transaction_type' => '',
            'amount_min' => '',
            'amount_max' => '',
            'method' => '',
            'reference_no' => '',
            'status' => '',
            'transaction_date_from' => '',
            'transaction_date_to' => '',
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
        return view('livewire.user-transaction-table');
    }
}
