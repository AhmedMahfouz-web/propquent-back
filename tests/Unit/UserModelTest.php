<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserTransaction;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_implements_jwt_subject()
    {
        $user = new User();
        $this->assertInstanceOf(JWTSubject::class, $user);
    }

    public function test_user_has_jwt_identifier()
    {
        $user = User::factory()->create();
        $this->assertEquals($user->id, $user->getJWTIdentifier());
    }

    public function test_user_has_jwt_custom_claims()
    {
        $user = new User();
        $this->assertIsArray($user->getJWTCustomClaims());
    }

    public function test_user_uses_password_hash_for_authentication()
    {
        $user = User::factory()->create([
            'password_hash' => bcrypt('password123')
        ]);
        
        $this->assertEquals($user->password_hash, $user->getAuthPassword());
    }

    public function test_user_fillable_attributes()
    {
        $fillableAttributes = [
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
        ];
        
        $user = new User();
        $this->assertEquals($fillableAttributes, $user->getFillable());
    }
}
