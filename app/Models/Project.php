<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\LogsActivity;

class Project extends Model
{
        use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'title',
        'developer_id',
        'location',
        'type',
        'unit_no',
        'project',
        'area',
        'garden_area',
        'bedrooms',
        'bathrooms',
        'floor',
        'status',
        'stage',
        'target_1',
        'target_2',
        'entry_date',
        'exit_date',
        'investment_type',
        'image_url',
        'document',
    ];

    /**
     * Get the developer that owns the project.
     */
    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    /**
     * Get the images for the project.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class);
    }

    /**
     * Get the status changes for the project.
     */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(StatusChange::class);
    }

    /**
     * Get the transactions for the project.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(ProjectTransaction::class, 'project_key', 'key');
    }
}

