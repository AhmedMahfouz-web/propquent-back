<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuthController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['login', 'register', 'forgotPassword', 'resetPassword']),
        ];
    }

    /**
     * Register new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password_hash' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'country' => $request->country,
                'is_active' => true,
                'email_verified' => false,
            ]);

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = [
                'email' => $request->email,
                'password' => $request->password
            ];

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Get the authenticated user as an Eloquent model
            $user = User::find(Auth::id());
            $user->update(['last_login_at' => now()]);

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60
                ]
            ]);

        } catch (\Exception $e) {
            Log::warning('Login attempt failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forgot password - send reset token
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $token = $user->generatePasswordResetToken();

            // Here you would typically send an email with the reset token
            // For now, we'll return it in the response (remove this in production)
            Log::info('Password reset requested', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset token generated',
                'data' => [
                    'reset_token' => $token, // Remove this in production
                    'expires_at' => $user->password_reset_expires_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Forgot password failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(ResetPasswordRequest $request, string $token): JsonResponse
    {
        try {
            $user = User::where('password_reset_token', $token)->first();
            
            if (!$user || !$user->isValidPasswordResetToken($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ], 400);
            }

            $user->update([
                'password_hash' => Hash::make($request->password)
            ]);
            
            $user->clearPasswordResetToken();

            Log::info('Password reset successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successful'
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Password reset failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user and blacklist token
     */
    public function logout(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get the current token
            $token = JWTAuth::getToken();
            
            // Invalidate/blacklist the token
            JWTAuth::invalidate($token);

            Log::info('User logged out successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token_blacklisted' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful - token has been blacklisted'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
