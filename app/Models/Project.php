<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Project extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasUuids;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image')->singleFile();
        $this->addMediaCollection('images');
    }

    public function images()
    {
        return $this->media()->where('collection_name', 'images');
    }

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

