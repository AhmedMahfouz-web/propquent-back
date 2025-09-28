<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTransaction extends Model
{
    use HasFactory;
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('latest_first', function ($builder) {
            $builder->orderBy('transaction_date', 'desc')->orderBy('created_at', 'desc');
        });
    }

    protected $fillable = [
        'user_id',
        'type',
        'amount',
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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
