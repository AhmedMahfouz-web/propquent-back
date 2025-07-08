<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\LogsActivity;

class ProjectTransaction extends Model
{
    protected $table = 'project_transaction';
        use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_key',
        'type',
        'serving',
        'what_id',
        'amount',
        'due_date',
        'actual_date',
        'transaction_date',
        'method',
        'reference_no',
        'status',
        'note',
    ];

    /**
     * Get the project that owns the transaction.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_key', 'key');
    }

    /**
     * Get the transaction what for the transaction.
     */
    public function what(): BelongsTo
    {
        return $this->belongsTo(TransactionWhat::class, 'what_id');
    }
}

