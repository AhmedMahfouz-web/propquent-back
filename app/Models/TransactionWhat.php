<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionWhat extends Model
{
    use HasFactory;

    protected $table = 'transaction_whats';

    protected $fillable = [
        'name',
        'description',
        'category',
        'financial_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function projectTransactions(): HasMany
    {
        return $this->hasMany(ProjectTransaction::class, 'what_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include revenue categories.
     */
    public function scopeRevenue($query)
    {
        return $query->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_REVENUE);
    }

    /**
     * Scope a query to only include expense categories.
     */
    public function scopeExpenses($query)
    {
        return $query->where('financial_type', ProjectTransaction::FINANCIAL_TYPE_EXPENSE);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if this is a revenue category.
     */
    public function isRevenue(): bool
    {
        return $this->financial_type === ProjectTransaction::FINANCIAL_TYPE_REVENUE;
    }

    /**
     * Check if this is an expense category.
     */
    public function isExpense(): bool
    {
        return $this->financial_type === ProjectTransaction::FINANCIAL_TYPE_EXPENSE;
    }

    /**
     * Get the formatted category name.
     */
    public function getFormattedCategory(): string
    {
        return ucfirst($this->category);
    }
}
