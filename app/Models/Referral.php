<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'referred_email',
        'status',
    ];

    /**
     * Get the user that made the referral.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

