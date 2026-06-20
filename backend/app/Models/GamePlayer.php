<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'is_alive',
        'self_heal_used',
        'is_winner',
        'xp_earned',
    ];

    protected $casts = [
        'is_alive' => 'boolean',
        'self_heal_used' => 'boolean',
        'is_winner' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Role helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Mafya ekibinde olan roller: mafia, godfather, silencer.
     */
    public function isMafia(): bool
    {
        return in_array($this->role, ['mafia', 'godfather', 'silencer'], true);
    }

    public function isSheriff(): bool
    {
        return $this->role === 'sheriff';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }
}
