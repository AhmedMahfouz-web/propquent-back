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

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->transactions = UserTransaction::with('user')
            ->get()
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
            'actual_date' => 'nullable|date',
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
        $requiredFields = ['user_id', 'transaction_type', 'amount', 'transaction_date', 'status'];

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
        $requiredFields = ['user_id', 'transaction_type', 'amount', 'transaction_date', 'status'];
        $hasAllRequired = true;

        foreach ($requiredFields as $field) {
            if (empty($row[$field])) {
                $hasAllRequired = false;
                break;
            }
        }

        if ($hasAllRequired) {
            // Validate the data
            $validator = Validator::make($row, [
                'user_id' => 'required|exists:users,id',
                'transaction_type' => 'required|in:' . implode(',', array_keys($this->transactionTypes)),
                'amount' => 'required|numeric|min:0.01',
                'transaction_date' => 'required|date',
                'actual_date' => 'nullable|date',
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

    public function render()
    {
        return view('livewire.user-transaction-table');
    }
}
