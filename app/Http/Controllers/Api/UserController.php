<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseApiController
{
    protected string $model = User::class;
    protected ?string $resource = UserResource::class;

    protected array $searchableFields = [
        'full_name',
        'email',
        'custom_id',
        'phone_number'
    ];

    protected array $filterableFields = [
        'is_active',
        'email_verified',
        'country',
        'theme_color'
    ];

    protected array $sortableFields = [
        'id',
        'full_name',
        'email',
        'custom_id',
        'created_at',
        'updated_at',
        'last_login_at'
    ];


    /**
     * Override validateStoreRequest to hash password
     */
    protected function validateStoreRequest(Request $request): array
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password_hash' => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'profile_picture_url' => 'nullable|url',
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
            'theme_color' => 'nullable|string|max:50',
            'custom_theme_color' => 'nullable|string|max:7',
        ]);

        // Hash password if provided
        if (isset($data['password_hash'])) {
            $data['password_hash'] = Hash::make($data['password_hash']);
        }

        return $data;
    }

    /**
     * Override validateUpdateRequest to hash password if provided
     */
    protected function validateUpdateRequest(Request $request, Model $resource): array
    {
        $data = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $resource->id,
            'password_hash' => 'sometimes|string|min:8',
            'phone_number' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'profile_picture_url' => 'nullable|url',
            'is_active' => 'boolean',
            'email_verified' => 'boolean',
            'theme_color' => 'nullable|string|max:50',
            'custom_theme_color' => 'nullable|string|max:7',
        ]);

        // Hash password if provided
        if (isset($data['password_hash'])) {
            $data['password_hash'] = Hash::make($data['password_hash']);
        }

        return $data;
    }
}
