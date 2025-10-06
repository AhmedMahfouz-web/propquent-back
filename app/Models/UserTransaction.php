<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTransaction extends Model
{
    use HasFactory;

    /**
     * Transaction types - restricted to deposit and withdrawal only
     */
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdraw';

    const STATUS_DONE = 'done';
    const STATUS_PENDING = 'pending';
    const STATUS_CANCELLED = 'cancelled';


    protected static function booted(): void
    {
        static::addGlobalScope('latest_first', function ($builder) {
            $builder->orderBy('transaction_date', 'desc')->orderBy('created_at', 'desc');
        });
    }

    protected $fillable = [
        'user_id',
        'transaction_type',
        'amount',
        'is_investment',
        'transaction_date',
        'actual_date',
        'method',
        'reference_no',
        'note',
        'status',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'actual_date' => 'date',
        'is_investment' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include deposit transactions.
     */
    public function scopeDeposits($query)
    {
        return $query->where('transaction_type', self::TYPE_DEPOSIT);
    }

    /**
     * Scope a query to only include withdrawal transactions.
     */
    public function scopeWithdrawals($query)
    {
        return $query->where('transaction_type', self::TYPE_WITHDRAWAL);
    }

    /**
     * Scope a query to only include investment transactions.
     */
    public function scopeInvestments($query)
    {
        return $query->where('is_investment', true);
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
     * Check if this is a deposit transaction.
     */
    public function isDeposit(): bool
    {
        return $this->transaction_type === self::TYPE_DEPOSIT;
    }

    /**
     * Check if this is a withdrawal transaction.
     */
    public function isWithdrawal(): bool
    {
        return $this->transaction_type === self::TYPE_WITHDRAWAL;
    }

    /**
     * Get the formatted transaction type.
     */
    public function getFormattedType(): string
    {
        return ucfirst($this->transaction_type);
    }

    /**
     * Get available transaction types
     */
    public static function getAvailableTransactionTypes(): array
    {
        return [
            self::TYPE_DEPOSIT => 'Deposit',
            self::TYPE_WITHDRAWAL => 'Withdrawal',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getAvailableStatuses(): array
    {
        try {
            $statuses = SystemConfiguration::getOptions('transaction_statuses');
            return $statuses ?: [
                'pending' => 'Pending',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
        } catch (\Exception $e) {
            return [
                'pending' => 'Pending',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
        }
    }

    public static function isValidStatus(string $status): bool
    {
        return array_key_exists($status, self::getAvailableStatuses());
    }

    /**
     * Get available methods
     */
    public static function getAvailableMethods(): array
    {
        try {
            $methods = SystemConfiguration::getOptions('transaction_methods');
            return $methods ?: [
                'cash' => 'Cash',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
                'card' => 'Card',
            ];
        } catch (\Exception $e) {
            return [
                'cash' => 'Cash',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
                'card' => 'Card',
            ];
        }
    }

    /**
     * Get validation rules for the model
     */
    public static function getValidationRules(): array
    {
        $transactionTypes = self::getAvailableTransactionTypes();
        $methods = self::getAvailableMethods();
        $statuses = self::getAvailableStatuses();

        return [
            'user_id' => 'required|exists:users,id',
            'transaction_type' => 'required|in:' . implode(',', array_keys($transactionTypes ?: [])),
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'actual_date' => 'nullable|date',
            'method' => 'nullable' . (!empty($methods) ? '|in:' . implode(',', array_keys($methods)) : ''),
            'reference_no' => 'nullable|string|max:255',
            'status' => 'required' . (!empty($statuses) ? '|in:' . implode(',', array_keys($statuses)) : ''),
            'note' => 'nullable|string|max:65535',
        ];
    }
}
