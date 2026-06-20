<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'round_number',
        'phase',
        'night_actions_submitted',
        'phase_ends_at',
        'winner',
        'player_count',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'night_actions_submitted' => 'boolean',
        'phase_ends_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'session_id');
    }

    public function alivePlayers(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'session_id')
            ->where('is_alive', true);
    }
}
