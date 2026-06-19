<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * `avatar` uses the model accessor which handles
     * the DiceBear fallback automatically.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'nickname'     => $this->nickname,
            'email'        => $this->email,
            'avatar'       => $this->avatar,   // accessor — dicebear fallback dahil
            'xp'           => $this->xp,
            'rank'         => $this->rank,
            'total_played' => $this->total_played,
            'total_wins'   => $this->total_wins,
            'total_losses' => $this->total_losses,
            'is_admin'     => $this->is_admin,
        ];
    }
}
