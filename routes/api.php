<?php

use App\Http\Controllers\Api\ThemeController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Admin API routes
Route::prefix('admin')->middleware(['auth:admins'])->group(function () {
    // Theme management routes
    Route::post('/theme', [ThemeController::class, 'update'])->name('api.admin.theme.update');
    Route::get('/theme', [ThemeController::class, 'show'])->name('api.admin.theme.show');
    Route::get('/themes', [ThemeController::class, 'themes'])->name('api.admin.themes');
});
