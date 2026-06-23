<?php

namespace App\Jobs;

use App\Models\GameSession;
use App\Services\GameEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DayVotePhaseEndJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $roundNumber,
    ) {}

    public function handle(GameEngine $gameEngine): void
    {
        $session = GameSession::find($this->sessionId);

        if (! $session) {
            return;
        }

        // STALE CHECK: round veya faz değiştiyse işlem yapma
        if ($session->round_number !== $this->roundNumber) {
            return;
        }

        if ($session->phase !== 'day_vote') {
            return;
        }

        // resolveDayVote() kendi içinde de faz kontrolü yapıyor (race condition koruması)
        $gameEngine->resolveDayVote($session);
    }
}
