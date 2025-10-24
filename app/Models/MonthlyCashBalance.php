<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyCashBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'month_date',
        'revenue',
        'expense',
        'deposits',
        'withdrawals',
        'cash_balance',
        'previous_month_cash',
    ];

    protected $casts = [
        'month_date' => 'date',
        'revenue' => 'decimal:2',
        'expense' => 'decimal:2',
        'deposits' => 'decimal:2',
        'withdrawals' => 'decimal:2',
        'cash_balance' => 'decimal:2',
        'previous_month_cash' => 'decimal:2',
    ];

    /**
     * Get cash balance for a specific month
     */
    public static function getCashForMonth(string $month): float
    {
        $record = self::where('month_date', $month)->first();
        return $record ? (float) $record->cash_balance : 0;
    }

    /**
     * Get cash balances for multiple months
     */
    public static function getCashForMonths(array $months): array
    {
        $records = self::whereIn('month_date', $months)->get()->keyBy('month_date');
        
        $result = [];
        foreach ($months as $month) {
            $result[$month] = $records->has($month) ? (float) $records[$month]->cash_balance : 0;
        }
        
        return $result;
    }
}
