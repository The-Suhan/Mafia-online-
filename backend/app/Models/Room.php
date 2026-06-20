<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'owner_id',
        'max_players',
        'current_players',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    /**
     * Bu odaya ait, henüz bitmemiş en güncel oyun oturumu (varsa).
     */
    public function activeSession(): HasOne
    {
        return $this->hasOne(GameSession::class)
            ->where('phase', '!=', 'finished')
            ->latestOfMany();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Lobide / odada bulunan kullanıcılar (room_players pivot).
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_players')
            ->withPivot('joined_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'waiting');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * 6 haneli, büyük harf + rakamdan oluşan, DB'de unique bir oda kodu üretir.
     * Örn: "X7K2A9"
     */
    public static function generateCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
