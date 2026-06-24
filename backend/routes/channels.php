<?php

use App\Models\GamePlayer;
use App\Models\Room;
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

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    $room = Room::find($roomId);

    if (! $room) {
        return false;
    }

    $isMember = $room->players()->where('user_id', $user->id)->exists();

    if (! $isMember) {
        return false;
    }

    return [
        'user_id' => $user->id,
        'nickname' => $user->nickname,
        'avatar' => $user->avatar,
        'is_alive' => true,
        'is_owner' => $room->owner_id === $user->id,
    ];
});
