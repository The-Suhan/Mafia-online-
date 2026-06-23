<?php

namespace App\Jobs;

use App\Events\PhaseChanged;
use App\Models\GameSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DayPhaseStartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $sessionId) {}

    public function handle(): void
    {
        $session = GameSession::find($this->sessionId);

        if (! $session) {
            return;
        }

        // Sadece day_announcement fazındaysa geç — başka bir şey olduysa dur
        if ($session->phase !== 'day_announcement') {
            return;
        }

        $endsAt = now()->addMinutes(2);

        $session->update([
            'phase'         => 'day_vote',
            'phase_ends_at' => $endsAt,
        ]);

        broadcast(new PhaseChanged(
            $session->id,
            'day_vote',
            $session->round_number,
            $endsAt,
        ));

        // 2 dakika sonra oylama çözümleme job'u
        DayVotePhaseEndJob::dispatch($session->id, $session->round_number)
            ->delay($endsAt);
    }
}
