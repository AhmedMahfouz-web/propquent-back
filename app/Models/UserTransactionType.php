<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserTransactionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
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
        return $this->hasMany(UserTransaction::class, 'transaction_type', 'key');
    }

    /**
     * Get all active transaction types as key-value pairs
     */
    public static function getOptions(): array
    {
        return static::pluck('name', 'key')->toArray();
    }

    /**
     * Check if a transaction type key is valid
     */
    public static function isValidKey(string $key): bool
    {
        return static::where('key', $key)->exists();
    }
}
