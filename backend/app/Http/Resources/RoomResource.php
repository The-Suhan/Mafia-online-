<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'max_players' => $this->max_players,
            'current_players' => $this->current_players,
            'status' => $this->status,
            'settings' => $this->settings,
            'active_session' => new GameSessionResource($this->whenLoaded('activeSession')),
            'created_at' => $this->created_at,
        ];
    }
}
