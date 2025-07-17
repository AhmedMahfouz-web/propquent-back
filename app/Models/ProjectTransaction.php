<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\LogsActivity;

class ProjectTransaction extends Model
{
    protected $table = 'project_transaction';
    use HasFactory;
    
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('latest_first', function ($builder) {
            $builder->orderBy('transaction_date', 'desc')->orderBy('created_at', 'desc');
        });
        
        // Add validation to prevent foreign key constraint violations
        static::creating(function ($transaction) {
            if ($transaction->project_key && !Project::where('key', $transaction->project_key)->exists()) {
                throw new \InvalidArgumentException("Project with key '{$transaction->project_key}' does not exist.");
            }
        });
        
        static::updating(function ($transaction) {
            if ($transaction->project_key && !Project::where('key', $transaction->project_key)->exists()) {
                throw new \InvalidArgumentException("Project with key '{$transaction->project_key}' does not exist.");
            }
        });
    }

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

