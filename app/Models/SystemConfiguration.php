<?php

namespace App\Models;

use App\Events\ConfigurationChanged;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SystemConfiguration extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category',
        'key',
        'value',
        'label',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope a query to only include configurations of a specific category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include active configurations.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order configurations by sort_order and label.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    /**
     * Get configurations by category as key-value pairs.
     */
    public static function getByCategory(string $category): Collection
    {
        return static::byCategory($category)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get configuration value by category and key.
     */
    public static function getValue(string $category, string $key, mixed $default = null): mixed
    {
        $config = static::byCategory($category)
            ->where('key', $key)
            ->active()
            ->first();

        return $config ? $config->value : $default;
    }

    /**
     * Get configuration options for dropdowns.
     */
    public static function getOptions(string $category): array
    {
        return static::byCategory($category)
            ->active()
            ->ordered()
            ->pluck('label', 'key')
            ->toArray();
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (SystemConfiguration $configuration) {
            ConfigurationChanged::dispatch($configuration, 'created');
        });

        static::updated(function (SystemConfiguration $configuration) {
            $oldValues = $configuration->getOriginal();
            ConfigurationChanged::dispatch($configuration, 'updated', $oldValues);
        });

        static::deleted(function (SystemConfiguration $configuration) {
            ConfigurationChanged::dispatch($configuration, 'deleted');
        });
    }
}
