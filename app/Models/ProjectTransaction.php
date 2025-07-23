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

    public const FINANCIAL_TYPE_REVENUE = 'revenue';
    public const FINANCIAL_TYPE_EXPENSE = 'expense';

    /**
     * Get available financial types from configuration
     */
    public static function getAvailableFinancialTypes(): array
    {
        return SystemConfiguration::getOptions('transaction_types');
    }

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
     * Check if a financial type is valid
     */
    public static function isValidFinancialType(string $type): bool
    {
        return array_key_exists($type, self::getAvailableFinancialTypes());
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
        'type',
        'financial_type',
        'serving',
        'what_id',
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

    public function transactionWhat(): BelongsTo
    {
        return $this->belongsTo(TransactionWhat::class, 'what_id');
    }

    /**
     * Scope a query to only include revenue transactions.
     */
    public function scopeRevenue($query)
    {
        return $query->where('financial_type', 'revenue');
    }

    /**
     * Scope a query to only include expense transactions.
     */
    public function scopeExpenses($query)
    {
        return $query->where('financial_type', 'expense');
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
     * Check if this is a revenue transaction.
     */
    public function isRevenue(): bool
    {
        return $this->financial_type === 'revenue';
    }

    /**
     * Check if this is an expense transaction.
     */
    public function isExpense(): bool
    {
        return $this->financial_type === 'expense';
    }

    /**
     * Get the formatted financial type.
     */
    public function getFormattedFinancialType(): string
    {
        return ucfirst($this->financial_type);
    }

    /**
     * Custom validation to prevent infinite recursion
     */
    protected function validateTransaction(): void
    {
        // Check for circular references in related transactions
        if ($this->project_key && $this->what_id) {
            $relatedTransactions = static::where('project_key', $this->project_key)
                ->where('what_id', $this->what_id)
                ->where('id', '!=', $this->id ?? 0)
                ->count();

            if ($relatedTransactions > 10) {
                throw ValidationException::withMessages([
                    'transaction' => 'Too many related transactions detected. Possible circular reference.'
                ]);
            }
        }

        // Validate amount is positive
        if ($this->amount && $this->amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Transaction amount must be positive.'
            ]);
        }

        // Validate dates are logical
        if ($this->due_date && $this->actual_date && $this->actual_date < $this->due_date) {
            // This is just a warning, not an error
            \Log::warning('Actual date is before due date for transaction', [
                'transaction_id' => $this->id,
                'due_date' => $this->due_date,
                'actual_date' => $this->actual_date
            ]);
        }
    }
}
