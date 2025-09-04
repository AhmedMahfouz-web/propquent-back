<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTransaction extends Model
{
    use HasFactory;

    /**
     * Legacy constants - kept for backward compatibility
     * Use database-driven values instead
     */

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
     * Get available transaction types from database
     */
    public static function getAvailableTransactionTypes(): array
    {
        return UserTransactionType::getOptions();
    }

    /**
     * Get available statuses from database
     */
    public static function getAvailableStatuses(): array
    {
        return UserTransactionStatus::getOptions();
    }

    /**
     * Check if transaction type is valid
     */
    public static function isValidTransactionType(string $type): bool
    {
        return UserTransactionType::isValidKey($type);
    }

    /**
     * Check if status is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return UserTransactionStatus::isValidKey($status);
    }

    /**
     * Get transaction type relationship
     */
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(UserTransactionType::class, 'transaction_type', 'key');
    }

    /**
     * Get status relationship
     */
    public function transactionStatus(): BelongsTo
    {
        return $this->belongsTo(UserTransactionStatus::class, 'status', 'key');
    }

    /**
     * Get available methods
     */
    public static function getAvailableMethods(): array
    {
        return [
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'wire_transfer' => 'Wire Transfer',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
        ];
    }
}
