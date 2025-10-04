<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->custom_id)) {
                $user->custom_id = static::generateCustomId();
            }
        });
    }

    /**
     * Generate a unique custom ID in the format inv-1, inv-2, etc.
     */
    protected static function generateCustomId(): string
    {
        $lastUser = DB::table('users')
            ->whereNotNull('custom_id')
            ->where('custom_id', 'like', 'inv-%')
            ->orderByRaw('CAST(SUBSTRING(custom_id, 5) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastUser && preg_match('/inv-(\d+)/', $lastUser->custom_id, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        return 'inv-' . $nextNumber;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'auth_provider',
        'provider_user_id',
        'email_verified',
        'phone_number',
        'country',
        'profile_picture_url',
        'is_active',
        'last_login_at',
        'theme_color',
        'custom_theme_color',
        'custom_id',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
        'password_reset_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password_reset_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(UserTransaction::class);
    }

    /**
     * Get the referrals for the user.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    /**
     * Get the profit distributions for the user.
     * Note: ProfitDistribution model may not exist yet
     */
    // public function profitDistributions(): HasMany
    // {
    //     return $this->hasMany(ProfitDistribution::class);
    // }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Get the password for the user.
     * Support both password and password_hash fields
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Generate password reset token
     */
    public function generatePasswordResetToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(1), // Token expires in 1 hour
        ]);
        return $token;
    }

    /**
     * Check if password reset token is valid
     */
    public function isValidPasswordResetToken(string $token): bool
    {
        return $this->password_reset_token === $token 
            && $this->password_reset_expires_at 
            && $this->password_reset_expires_at->isFuture();
    }

    /**
     * Clear password reset token
     */
    public function clearPasswordResetToken(): void
    {
        $this->update([
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);
    }
}