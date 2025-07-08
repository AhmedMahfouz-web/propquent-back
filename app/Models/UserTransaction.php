<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\LogsActivity;

class UserTransaction extends Model
{
        use HasFactory, LogsActivity;

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
