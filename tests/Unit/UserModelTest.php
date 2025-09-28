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

    public function test_user_has_transactions_relationship()
    {
        $user = User::factory()->create();
        $transaction = UserTransaction::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->transactions()->exists());
        $this->assertEquals(1, $user->transactions()->count());
        $this->assertEquals($transaction->id, $user->transactions->first()->id);
    }

    public function test_user_has_referrals_relationship()
    {
        $user = User::factory()->create();
        $referral = Referral::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->referrals()->exists());
        $this->assertEquals(1, $user->referrals()->count());
        $this->assertEquals($referral->id, $user->referrals->first()->id);
    }

    public function test_user_has_soft_deletes()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        // User should be soft deleted
        $this->assertSoftDeleted('users', ['id' => $userId]);

        // User should still be retrievable with trashed
        $this->assertNotNull(User::withTrashed()->find($userId));
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

    public function test_user_casts_attributes_correctly()
    {
        $user = User::factory()->create([
            'email_verified' => true,
            'is_active' => false,
            'last_login_at' => now(),
        ]);

        $this->assertIsBool($user->email_verified);
        $this->assertIsBool($user->is_active);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->last_login_at);
    }
}
