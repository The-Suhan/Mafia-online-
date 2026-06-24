<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nickname',
        'email',
        'password',
        'avatar_url',
        'xp',
        'rank',
        'total_played',
        'total_wins',
        'total_losses',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'xp' => 'integer',
        'total_played' => 'integer',
        'total_wins' => 'integer',
        'total_losses' => 'integer',
    ];

    // ─────────────────────────────────────────────
    //  Accessors
    // ─────────────────────────────────────────────

    /**
     * Avatar accessor: returns avatar_url if set,
     * otherwise falls back to a DiceBear pixel-art avatar.
     */
    public function getAvatarAttribute(): string
    {
        return $this->avatar_url
            ?? 'https://api.dicebear.com/7.x/pixel-art/svg?seed='.urlencode($this->nickname);
    }

    // ─────────────────────────────────────────────
    //  XP & Rank
    // ─────────────────────────────────────────────

    /**
     * Add XP to the user, recalculate rank, and persist.
     */
    public function addXp(int $amount): void
    {
        $this->xp += $amount;
        $this->rank = self::calculateRank($this->xp);
        $this->save();
    }

    /**
     * Determine rank from an XP value.
     *
     * 0–199    → rookie
     * 200–499  → novice
     * 500–1199 → elite
     * 1200–2499→ pro
     * 2500–4999→ master
     * 5000+    → legend
     */
    public static function calculateRank(int $xp): string
    {
        return match (true) {
            $xp >= 5000 => 'legend',
            $xp >= 2500 => 'master',
            $xp >= 1200 => 'pro',
            $xp >= 500 => 'elite',
            $xp >= 200 => 'novice',
            default => 'rookie',
        };
    }

    /**
     * Return the XP threshold for the next rank above the user's current XP.
     * Used by AdminController::userDetail for `xp_to_next_level`.
     * Falls back to xp + 1000 if already at the top rank.
     */
    public static function nextRankThreshold(int $xp): int
    {
        $thresholds = [200, 500, 1200, 2500, 5000];

        foreach ($thresholds as $threshold) {
            if ($xp < $threshold) {
                return $threshold;
            }
        }

        return $xp + 1000;
    }

    // ─────────────────────────────────────────────
    //  Password Reset — custom notification
    // ─────────────────────────────────────────────

    /**
     * Send the password reset notification with a frontend URL.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
