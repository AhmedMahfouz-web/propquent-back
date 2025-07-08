<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusChange extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'from_status',
        'to_status',
        'change_date',
    ];

    /**
     * Get the project that owns the status change.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

