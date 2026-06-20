<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'round_number' => $this->round_number,
            'phase' => $this->phase,
            'phase_ends_at' => $this->phase_ends_at,
            'winner' => $this->winner,
            'player_count' => $this->player_count,
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
        ];
    }
}
