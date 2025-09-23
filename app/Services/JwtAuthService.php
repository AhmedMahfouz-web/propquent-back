<?php

namespace App\Services;

use App\Models\JwtBlacklist;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Carbon\Carbon;

class JwtAuthService
{
    /**
     * Attempt to authenticate user and return token
     */
    public function login(array $credentials, string $guard = 'web'): array
    {
        if (!$token = Auth::guard($guard)->attempt($credentials)) {
            throw new \Exception('Invalid credentials', 401);
        }

        $user = Auth::guard($guard)->user();
        
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // Convert minutes to seconds
            'user' => $user
        ];
    }

    /**
     * Logout user and blacklist token
     */
    public function logout(): void
    {
        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token);
            
            // Get token expiration time
            $exp = $payload->get('exp');
            $expiresAt = Carbon::createFromTimestamp($exp);
            
            // Add token to blacklist
            JwtBlacklist::blacklistToken($payload->get('jti'), $expiresAt);
            
            // Invalidate the token
            JWTAuth::invalidate($token);
            
        } catch (JWTException $e) {
            throw new \Exception('Failed to logout', 500);
        }
    }

    /**
     * Refresh the token
     */
    public function refresh(): array
    {
        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token);
            
            // Blacklist the old token
            $exp = $payload->get('exp');
            $expiresAt = Carbon::createFromTimestamp($exp);
            JwtBlacklist::blacklistToken($payload->get('jti'), $expiresAt);
            
            // Generate new token
            $newToken = JWTAuth::refresh($token);
            
            return [
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ];
            
        } catch (TokenExpiredException $e) {
            throw new \Exception('Token has expired', 401);
        } catch (TokenInvalidException $e) {
            throw new \Exception('Token is invalid', 401);
        } catch (JWTException $e) {
            throw new \Exception('Could not refresh token', 500);
        }
    }

    /**
     * Get the authenticated user
     */
    public function me()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                throw new \Exception('User not found', 404);
            }
            
            return $user;
            
        } catch (TokenExpiredException $e) {
            throw new \Exception('Token has expired', 401);
        } catch (TokenInvalidException $e) {
            throw new \Exception('Token is invalid', 401);
        } catch (JWTException $e) {
            throw new \Exception('Token not provided', 401);
        }
    }

    /**
     * Check if token is blacklisted
     */
    public function isTokenBlacklisted(): bool
    {
        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token);
            $jti = $payload->get('jti');
            
            return JwtBlacklist::isBlacklisted($jti);
            
        } catch (JWTException $e) {
            return true; // If we can't get the token, consider it blacklisted
        }
    }

    /**
     * Validate token and check blacklist
     */
    public function validateToken(): bool
    {
        try {
            // Check if token exists and is valid
            $token = JWTAuth::getToken();
            if (!$token) {
                return false;
            }

            // Parse and validate token
            $payload = JWTAuth::getPayload($token);
            
            // Check if token is blacklisted
            $jti = $payload->get('jti');
            if (JwtBlacklist::isBlacklisted($jti)) {
                return false;
            }

            // Check if user exists
            if (!JWTAuth::authenticate($token)) {
                return false;
            }

            return true;
            
        } catch (TokenExpiredException $e) {
            return false;
        } catch (TokenInvalidException $e) {
            return false;
        } catch (JWTException $e) {
            return false;
        }
    }
}
