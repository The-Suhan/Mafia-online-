<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
//  Public routes (auth gerekmez)
// ─────────────────────────────────────────────

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ─────────────────────────────────────────────
//  Protected routes (Sanctum auth gerekir)
// ─────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Room & Game routes
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{room}', [RoomController::class, 'show']);
    Route::post('/rooms/{code}/join', [RoomController::class, 'joinByCode']);
    Route::post('/rooms/{room}/start', [RoomController::class, 'startGame']);
    Route::post('/rooms/{room}/close', [RoomController::class, 'closeRoom']);
    Route::post('/rooms/{room}/leave', [RoomController::class, 'leaveRoom']);
    Route::get('/rooms/{room}/session', [RoomController::class, 'getSession']);
    Route::get('/sessions/{session}/players', [RoomController::class, 'getPlayers']);
    Route::get('/rooms/{room}/messages', [RoomController::class, 'getMessages']);
    Route::post('/rooms/{room}/messages', [RoomController::class, 'postMessage']);
    Route::post('/game/{session}/night-action', [GameController::class, 'nightAction']);
    Route::post('/game/{session}/vote', [GameController::class, 'vote']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'stats']);
    Route::get('/activity', [AdminController::class, 'activity']);

    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/users/{id}', [AdminController::class, 'userDetail']);
    Route::get('/users/{id}/games', [AdminController::class, 'userGames']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    Route::patch('/users/{id}/xp', [AdminController::class, 'updateXp']);
    Route::patch('/users/{id}/role', [AdminController::class, 'updateRole']);

    Route::get('/sessions', [AdminController::class, 'sessions']);
    Route::get('/sessions/{id}', [AdminController::class, 'sessionDetail']);
    Route::get('/sessions/{id}/logs', [AdminController::class, 'sessionLogs']);
});
