<?php

use App\Models\GamePlayer;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('player.{userId}', function ($user, $userId) {
    return $user->id === (int) $userId;
});

Broadcast::channel('session.{sessionId}', function ($user, $sessionId) {
    return GamePlayer::where('session_id', $sessionId)
        ->where('user_id', $user->id)
        ->exists();
});
