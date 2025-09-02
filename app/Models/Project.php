<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Project extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const STATUS_EXITED = 'exited';

    /**
     * Get available project statuses from configuration
     */
    public static function getAvailableStatuses(): array
    {
        return SystemConfiguration::getOptions('project_statuses');
    }

    /**
     * Get available project stages from configuration
     */
    public static function getAvailableStages(): array
    {
        return SystemConfiguration::getOptions('project_stages');
    }

    /**
     * Get available project targets from configuration
     */
    public static function getAvailableTargets(): array
    {
        return SystemConfiguration::getOptions('project_targets');
    }

    /**
     * Check if a status is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return array_key_exists($status, self::getAvailableStatuses());
    }

    /**
     * Check if a stage is valid
     */
    public static function isValidStage(string $stage): bool
    {
        return array_key_exists($stage, self::getAvailableStages());
    }

    /**
     * Check if a target is valid
     */
    public static function isValidTarget(string $target): bool
    {
        return array_key_exists($target, self::getAvailableTargets());
    }

    /**
     * Get available property types from configuration
     */
    public static function getAvailablePropertyTypes(): array
    {
        return SystemConfiguration::getOptions('property_types');
    }

    /**
     * Get available investment types from configuration
     */
    public static function getAvailableInvestmentTypes(): array
    {
        return SystemConfiguration::getOptions('investment_types');
    }

    /**
     * Check if a property type is valid
     */
    public static function isValidPropertyType(string $type): bool
    {
        return array_key_exists($type, self::getAvailablePropertyTypes());
    }

    /**
     * Check if an investment type is valid
     */
    public static function isValidInvestmentType(string $type): bool
    {
        return array_key_exists($type, self::getAvailableInvestmentTypes());
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'project_key',
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

    protected $rules = [
        'project_key' => 'nullable|unique:projects,project_key|regex:/^[a-zA-Z0-9_-]{3,50}$/',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'entry_date' => 'date',
        'exit_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->id)) {
                $project->id = (string) Str::uuid();
            }

            // Validate project_key if provided
            if ($project->project_key && !self::validateProjectKey($project->project_key)) {
                throw new \InvalidArgumentException('Project key must be 3-50 characters long and contain only alphanumeric characters, hyphens, and underscores.');
            }

            // Validate exit_date is newer than entry_date
            if ($project->entry_date && $project->exit_date && $project->exit_date <= $project->entry_date) {
                throw new \InvalidArgumentException('Exit date must be newer than entry date.');
            }
        });

        static::updating(function (Project $project) {
            // Validate project_key if being updated
            if ($project->isDirty('project_key') && $project->project_key && !self::validateProjectKey($project->project_key)) {
                throw new \InvalidArgumentException('Project key must be 3-50 characters long and contain only alphanumeric characters, hyphens, and underscores.');
            }

            // Validate exit_date is newer than entry_date
            if ($project->entry_date && $project->exit_date && $project->exit_date <= $project->entry_date) {
                throw new \InvalidArgumentException('Exit date must be newer than entry date.');
            }
        });
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ProjectTransaction::class, 'project_key', 'key');
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(StatusChange::class);
    }


    public function evaluations(): HasMany
    {
        return $this->hasMany(ProjectEvaluation::class, 'project_key', 'key');
    }

    /**
     * Get the display identifier for the project (project_key if available, otherwise UUID).
     */
    public function getDisplayIdentifier(): string
    {
        return $this->project_key ?? $this->id;
    }

    /**
     * Validate project key format.
     */
    public static function validateProjectKey(string $projectKey): bool
    {
        // Allow alphanumeric characters, hyphens, and underscores, 3-50 characters
        return preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $projectKey);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->sharpen(10);
    }
}
