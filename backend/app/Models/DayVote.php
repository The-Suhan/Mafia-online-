<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DayVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'round_number',
        'voter_id',
        'target_id',
    ];

    /*
     * DB unique constraint (beklenen): session_id + round_number + voter_id
     * Bu sayede updateOrCreate ile tekrar oy kullanılabilir (oy değiştirme).
     */

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    /** Oy kullanan kullanıcı */
    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    /** Oy verilen kullanıcı */
    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }
}
