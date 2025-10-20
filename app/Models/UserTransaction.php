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
        $statuses = SystemConfiguration::getOptions('transaction_statuses');
        
        // Auto-seed if no system configurations are found
        if (empty($statuses)) {
            self::seedTransactionStatuses();
            $statuses = SystemConfiguration::getOptions('transaction_statuses');
        }
        
        // Final fallback
        if (empty($statuses)) {
            return [
                'done' => 'Done',
                'pending' => 'Pending',
                'cancelled' => 'Cancelled',
            ];
        }
        
        return $statuses;
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
        $methods = SystemConfiguration::getOptions('transaction_methods');
        
        // Auto-seed if no system configurations are found
        if (empty($methods)) {
            self::seedTransactionMethods();
            $methods = SystemConfiguration::getOptions('transaction_methods');
        }
        
        // Final fallback
        if (empty($methods)) {
            return [
                'bank_transfer' => 'Bank Transfer',
                'check' => 'Check',
                'cash' => 'Cash',
                'credit_card' => 'Credit Card',
                'instapay' => 'Instapay',
            ];
        }
        
        return $methods;
    }
    
    /**
     * Auto-seed transaction statuses
     */
    private static function seedTransactionStatuses(): void
    {
        $statuses = [
            ['category' => 'transaction_statuses', 'key' => 'done', 'value' => 'Done', 'label' => 'Done'],
            ['category' => 'transaction_statuses', 'key' => 'pending', 'value' => 'Pending', 'label' => 'Pending'],
            ['category' => 'transaction_statuses', 'key' => 'cancelled', 'value' => 'Cancelled', 'label' => 'Cancelled'],
        ];
        
        foreach ($statuses as $status) {
            SystemConfiguration::updateOrCreate(
                ['category' => $status['category'], 'key' => $status['key']],
                array_merge($status, ['is_active' => true])
            );
        }
    }
    
    /**
     * Auto-seed transaction methods
     */
    private static function seedTransactionMethods(): void
    {
        $methods = [
            ['category' => 'transaction_methods', 'key' => 'bank_transfer', 'value' => 'Bank Transfer', 'label' => 'Bank Transfer'],
            ['category' => 'transaction_methods', 'key' => 'check', 'value' => 'Check', 'label' => 'Check'],
            ['category' => 'transaction_methods', 'key' => 'cash', 'value' => 'Cash', 'label' => 'Cash'],
            ['category' => 'transaction_methods', 'key' => 'credit_card', 'value' => 'Credit Card', 'label' => 'Credit Card'],
            ['category' => 'transaction_methods', 'key' => 'instapay', 'value' => 'Instapay', 'label' => 'Instapay'],
        ];
        
        foreach ($methods as $method) {
            SystemConfiguration::updateOrCreate(
                ['category' => $method['category'], 'key' => $method['key']],
                array_merge($method, ['is_active' => true])
            );
        }
    }

}
