<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new User([
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'password_hash' => !empty($row['password']) ? Hash::make($row['password']) : Hash::make('password123'),
            'auth_provider' => 'local',
            'provider_user_id' => null,
            'email_verified' => true,
            'phone_number' => !empty($row['phone_number']) ? (string) $row['phone_number'] : null,
            'country' => $row['country'] ?? null,
            'profile_picture_url' => $row['profile_picture_url'] ?? null,
            'is_active' => true,
            'last_login_at' => null,
            'theme_color' => $row['theme_color'] ?? null,
            'custom_theme_color' => $row['custom_theme_color'] ?? null,
            'custom_id' => $row['custom_id'] ?? null, // Will be auto-generated if empty
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:6',
            'auth_provider' => 'nullable|string|max:50',
            'provider_user_id' => 'nullable|string|max:255',
            'email_verified' => 'nullable|boolean',
            'phone_number' => 'nullable|max:20',
            'country' => 'nullable|string|max:100',
            'profile_picture_url' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
            'last_login_at' => 'nullable|date',
            'theme_color' => 'nullable|string|max:50',
            'custom_theme_color' => 'nullable|string|max:7',
            'custom_id' => 'nullable|string|max:50|unique:users,custom_id',
        ];
    }
}
