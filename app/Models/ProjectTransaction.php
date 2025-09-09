<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProjectTransaction extends Model
{
    use HasFactory;


    /**
     * Get available serving types from configuration
     */
    public static function getAvailableServingTypes(): array
    {
        return SystemConfiguration::getOptions('transaction_serving');
    }

    /**
     * Get available transaction statuses from configuration
     */
    public static function getAvailableStatuses(): array
    {
        return SystemConfiguration::getOptions('transaction_statuses');
    }

    /**
     * Get available transaction methods from configuration
     */
    public static function getAvailableTransactionMethods(): array
    {
        return SystemConfiguration::getOptions('transaction_methods');
    }


    /**
     * Check if a serving type is valid
     */
    public static function isValidServingType(string $serving): bool
    {
        return array_key_exists($serving, self::getAvailableServingTypes());
    }

    /**
     * Check if a status is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return array_key_exists($status, self::getAvailableStatuses());
    }

    /**
     * Check if a transaction method is valid
     */
    public static function isValidTransactionMethod(string $method): bool
    {
        return array_key_exists($method, self::getAvailableTransactionMethods());
    }

    protected $table = 'project_transactions';

    protected $fillable = [
        'project_key',
        'serving',
        'amount',
        'transaction_category',
        'due_date',
        'actual_date',
        'transaction_date',
        'method',
        'reference_no',
        'status',
        'note',
    ];

    /**
     * Get validation rules for the model
     */
    public static function getValidationRules(): array
    {
        return [
            'project_key' => 'required|exists:projects,key',
            'serving' => 'nullable|in:' . implode(',', array_keys(self::getAvailableServingTypes())),
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'due_date' => 'nullable|date',
            'actual_date' => 'nullable|date',
            'method' => 'nullable|in:' . implode(',', array_keys(self::getAvailableTransactionMethods())),
            'reference_no' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', array_keys(self::getAvailableStatuses())),
            'note' => 'nullable|string|max:65535',
        ];
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'actual_date' => 'date',
        'transaction_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (ProjectTransaction $transaction) {
            $transaction->validateTransaction();
        });

        static::updating(function (ProjectTransaction $transaction) {
            $transaction->validateTransaction();
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_key', 'key');
    }



    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('transaction_category', $category);
    }

    /**
     * Scope a query to filter by month.
     */
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month);
    }

    /**
     * Scope a query to filter by year-month string (YYYY-MM).
     */
    public function scopeForYearMonth($query, $yearMonth)
    {
        list($year, $month) = explode('-', $yearMonth);
        return $query->forMonth($year, $month);
    }


    /**
     * Custom validation to prevent infinite recursion
     */
    protected function validateTransaction(): void
    {
        // Validate amount is positive
        if ($this->amount && $this->amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Transaction amount must be positive.'
            ]);
        }

        // Validate dates are logical
        if ($this->due_date && $this->actual_date && $this->actual_date < $this->due_date) {
            // This is just a warning, not an error
            Log::warning('Actual date is before due date for transaction', [
                'transaction_id' => $this->id,
                'due_date' => $this->due_date,
                'actual_date' => $this->actual_date
            ]);
        }
    }
}
