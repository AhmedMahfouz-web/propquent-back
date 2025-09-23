<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\JwtAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected JwtAuthService $jwtAuthService;

    public function __construct(JwtAuthService $jwtAuthService)
    {
        $this->jwtAuthService = $jwtAuthService;
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
                'guard' => 'sometimes|string|in:web,admins' // Optional guard selection
            ]);

            $credentials = $request->only('email', 'password');
            $guard = $request->get('guard', 'web');

            $authData = $this->jwtAuthService->login($credentials, $guard);

            Log::info('User logged in successfully', [
                'user_id' => $authData['user']->id,
                'email' => $authData['user']->email,
                'guard' => $guard
            ]);

            return ApiResponse::success(
                $authData,
                'Login successful'
            );

        } catch (ValidationException $e) {
            return ApiResponse::validationError(
                $e->errors(),
                'Invalid input data'
            );
        } catch (\Exception $e) {
            Log::warning('Login attempt failed', [
                'email' => $request->get('email'),
                'error' => $e->getMessage()
            ]);

            return ApiResponse::unauthorized('Invalid credentials');
        }
    }

    /**
     * Register new user (if registration is enabled)
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'sometimes|string|max:20',
            ]);

            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
            ]);

            // Auto-login after registration
            $authData = $this->jwtAuthService->login([
                'email' => $request->email,
                'password' => $request->password
            ]);

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return ApiResponse::created(
                $authData,
                'Registration successful'
            );

        } catch (ValidationException $e) {
            return ApiResponse::validationError(
                $e->errors(),
                'Invalid input data'
            );
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'email' => $request->get('email'),
                'error' => $e->getMessage()
            ]);

            return ApiResponse::serverError('Registration failed');
        }
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        try {
            $user = $this->jwtAuthService->me();
            
            $this->jwtAuthService->logout();

            Log::info('User logged out successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return ApiResponse::success(
                null,
                'Logout successful'
            );

        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage()
            ]);

            return ApiResponse::serverError('Logout failed');
        }
    }

    /**
     * Refresh token
     */
    public function refresh(): JsonResponse
    {
        try {
            $authData = $this->jwtAuthService->refresh();

            return ApiResponse::success(
                $authData,
                'Token refreshed successfully'
            );

        } catch (\Exception $e) {
            Log::warning('Token refresh failed', [
                'error' => $e->getMessage()
            ]);

            return ApiResponse::unauthorized($e->getMessage());
        }
    }

    /**
     * Get authenticated user
     */
    public function me(): JsonResponse
    {
        try {
            $user = $this->jwtAuthService->me();

            return ApiResponse::success(
                $user,
                'User data retrieved successfully'
            );

        } catch (\Exception $e) {
            return ApiResponse::unauthorized($e->getMessage());
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = $this->jwtAuthService->me();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return ApiResponse::error('Current password is incorrect', 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Log::info('Password changed successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return ApiResponse::success(
                null,
                'Password changed successfully'
            );

        } catch (ValidationException $e) {
            return ApiResponse::validationError(
                $e->errors(),
                'Invalid input data'
            );
        } catch (\Exception $e) {
            Log::error('Password change failed', [
                'error' => $e->getMessage()
            ]);

            return ApiResponse::serverError('Password change failed');
        }
    }
}
