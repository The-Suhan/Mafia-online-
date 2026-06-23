<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NightAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'round_number',
        'actor_id',
        'target_id',
        'action_type',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    /*
     * DB unique constraint (beklenen): session_id + round_number + actor_id
     * Bu sayede updateOrCreate çakışmadan çalışır.
     */

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    /** Aksiyonu gerçekleştiren kullanıcı */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** Hedef kullanıcı */
    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
