<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ThemeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    
    // Protected auth routes
    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->name('api.auth.change-password');
    });
});

// Protected API Routes
Route::middleware(['jwt.auth'])->group(function () {
    
    // User Management Routes
    Route::apiResource('users', UserController::class);
    
    // Project Management Routes
    Route::apiResource('projects', ProjectController::class);
    
    // Project Transactions Routes
    Route::prefix('projects/{project}')->group(function () {
        Route::get('/transactions', [ProjectController::class, 'transactions'])->name('api.projects.transactions');
        Route::post('/transactions', [ProjectController::class, 'storeTransaction'])->name('api.projects.transactions.store');
    });
    
    // User Transactions Routes
    Route::prefix('users/{user}')->group(function () {
        Route::get('/transactions', [UserController::class, 'transactions'])->name('api.users.transactions');
        Route::post('/transactions', [UserController::class, 'storeTransaction'])->name('api.users.transactions.store');
    });
    
    // System Information Routes
    Route::prefix('system')->group(function () {
        Route::get('/info', function () {
            return response()->json([
                'success' => true,
                'data' => [
                    'version' => '1.0.0',
                    'environment' => app()->environment(),
                    'timezone' => config('app.timezone'),
                    'locale' => config('app.locale'),
                ],
                'timestamp' => now()->toISOString(),
            ]);
        })->name('api.system.info');
    });
});

// Admin API routes (separate authentication)
Route::prefix('admin')->middleware(['auth:admins'])->group(function () {
    // Theme management routes
    Route::post('/theme', [ThemeController::class, 'update'])->name('api.admin.theme.update');
    Route::get('/theme', [ThemeController::class, 'show'])->name('api.admin.theme.show');
    Route::get('/themes', [ThemeController::class, 'themes'])->name('api.admin.themes');
});

// Health Check Route (Public)
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
})->name('api.health');
