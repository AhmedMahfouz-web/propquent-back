<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    /**
     * Available theme colors
     */
    private const AVAILABLE_THEMES = [
        'blue',
        'green',
        'purple',
        'orange',
        'red',
        'indigo',
        'pink',
        'teal'
    ];

    /**
     * Update user's theme preference
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'theme' => ['required', 'string', Rule::in(self::AVAILABLE_THEMES)],
            ]);

            $user = Auth::guard('admins')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Update user's theme preference
            $user->update([
                'theme_color' => $request->theme
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Theme updated successfully',
                'data' => [
                    'theme' => $request->theme,
                    'user_id' => $user->id
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid theme selection',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Theme update failed', [
                'user_id' => Auth::guard('admins')->id(),
                'theme' => $request->theme ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update theme preference'
            ], 500);
        }
    }

    /**
     * Get user's current theme
     */
    public function show(): JsonResponse
    {
        try {
            $user = Auth::guard('admins')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'theme' => $user->theme_color ?? 'blue',
                    'available_themes' => self::AVAILABLE_THEMES
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get user theme', [
                'user_id' => Auth::guard('admins')->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve theme preference'
            ], 500);
        }
    }

    /**
     * Get available themes
     */
    public function themes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'themes' => [
                    'blue' => ['name' => 'Blue', 'color' => '#3b82f6'],
                    'green' => ['name' => 'Green', 'color' => '#10b981'],
                    'purple' => ['name' => 'Purple', 'color' => '#8b5cf6'],
                    'orange' => ['name' => 'Orange', 'color' => '#f59e0b'],
                    'red' => ['name' => 'Red', 'color' => '#ef4444'],
                    'indigo' => ['name' => 'Indigo', 'color' => '#6366f1'],
                    'pink' => ['name' => 'Pink', 'color' => '#ec4899'],
                    'teal' => ['name' => 'Teal', 'color' => '#14b8a6'],
                ]
            ]
        ]);
    }
}
