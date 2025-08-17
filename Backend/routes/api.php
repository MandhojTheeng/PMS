<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']); // current user
    Route::get('/users', [AuthController::class, 'allUsers']); // role-aware users
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
});

// Delete users - Super Admin only (outside nested group to avoid issues)
Route::middleware(['auth:sanctum', 'role:Super Admin'])
    ->delete('/users', [AuthController::class, 'deleteAllUsers']);
