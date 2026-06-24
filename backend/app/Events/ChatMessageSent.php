<?php

namespace App\Events;

use App\Http\Resources\UserResource;
use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $message
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.'.$this->message->room_id),
        ];
    }

    public function broadcastAs(): string
    {
        return '.chat.message';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'room_id' => $this->message->room_id,
            'session_id' => $this->message->session_id,
            'user_id' => $this->message->user_id,
            'user' => $this->message->user ? new UserResource($this->message->user) : null,
            'message' => $this->message->message,
            'type' => $this->message->type,
            'round_number' => $this->message->round_number,
            'created_at' => $this->message->created_at,
        ];
    }
}
