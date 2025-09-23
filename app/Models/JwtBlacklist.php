<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JwtBlacklist extends Model
{
    protected $table = 'jwt_blacklist';

    protected $fillable = [
        'jti',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    /**
     * Check if a token is blacklisted
     */
    public static function isBlacklisted(string $jti): bool
    {
        return self::where('jti', $jti)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Add a token to the blacklist
     */
    public static function blacklistToken(string $jti, Carbon $expiresAt): void
    {
        self::create([
            'jti' => $jti,
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * Clean up expired tokens from blacklist
     */
    public static function cleanup(): int
    {
        return self::where('expires_at', '<=', now())->delete();
    }
}
