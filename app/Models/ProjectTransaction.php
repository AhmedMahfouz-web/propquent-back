<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ProjectTransaction extends Model
{
    protected $table = 'project_transaction';
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    /**
     * Flag to prevent infinite recursion during validation.
     */
    protected static $validating = false;

    protected static function booted(): void
    {
        // Temporarily disabled global scope to test for infinite loops
        // static::addGlobalScope('latest_first', function ($builder) {
        //     $builder->orderBy('transaction_date', 'desc')->orderBy('created_at', 'desc');
        // });

        $validator = function ($transaction) {
            if (static::$validating) {
                return;
            }

            static::$validating = true;

            try {
                if ($transaction->project_key && !DB::table('projects')->where('key', $transaction->project_key)->exists()) {
                    throw new \InvalidArgumentException("Project with key '{$transaction->project_key}' does not exist.");
                }
            } finally {
                static::$validating = false;
            }
        };

        // static::creating($validator);
        // static::updating($validator);
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
