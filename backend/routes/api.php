<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TravelRequestController;
use App\Http\Controllers\API\NotificationController;

// Teste para verificar se as rotas API estão funcionando
Route::get('/test', function() {
    return response()->json(['message' => 'API route is working!']);
});

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Travel Request routes
    Route::get('travel-requests/stats', [TravelRequestController::class, 'getStats']);
    Route::apiResource('travel-requests', TravelRequestController::class);
    Route::patch('travel-requests/{id}/status', [TravelRequestController::class, 'updateStatus']);

    // Notification routes
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
});
