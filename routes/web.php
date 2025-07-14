<?php

use Illuminate\Support\Facades\Route;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

Route::get('/create-temp-admin', function () {
    if (Admin::where('email', 'admin@gmail.com')->exists()) {
        return 'Admin user already exists.';
    }
    Admin::create([
        'name' => 'Super Admin',
        'email' => 'admin@gmail.com',
        'password_hash' => Hash::make('123456'),
    ]);
    return 'Admin user created successfully!';
});

Route::get('/', function () {
    return view('welcome');
});
