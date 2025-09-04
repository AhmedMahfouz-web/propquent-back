<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserTransactionStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', function ($builder) {
            $builder->where('is_active', true);
        });

        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('sort_order')->orderBy('name');
        });
    }

    public function userTransactions(): HasMany
    {
        return $this->hasMany(UserTransaction::class, 'status', 'key');
    }

    /**
     * Get all active statuses as key-value pairs
     */
    public static function getOptions(): array
    {
        return static::pluck('name', 'key')->toArray();
    }

    /**
     * Check if a status key is valid
     */
    public static function isValidKey(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Get status color for UI
     */
    public function getColorAttribute($value): string
    {
        return $value ?: 'gray';
    }
}
