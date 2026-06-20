<?php

use App\Http\Controllers\Api\AuthController;
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
});
