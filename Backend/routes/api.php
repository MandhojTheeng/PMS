<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']); // Current user info
    Route::get('/users', [AuthController::class, 'allUsers']); // Users list
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware(['role:Super Admin'])->get('/super-admin', function () {
        return response()->json(['message' => 'Super Admin access']);
    });

    Route::middleware(['role:Admin'])->get('/admin', function () {
        return response()->json(['message' => 'Admin access']);
    });

    Route::middleware(['role:User'])->get('/user-role', function () {
        return response()->json(['message' => 'User access']);
    });

    // Delete users (Super Admin only)
    Route::middleware(['role:Super Admin'])
        ->delete('/users', [AuthController::class, 'deleteAllUsers']);
});
