<?php

namespace App\Jobs;

use App\Models\GameSession;
use App\Services\GameEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NightResolutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $roundNumber,
    ) {}

    public function handle(GameEngine $gameEngine): void
    {
        $session = GameSession::find($this->sessionId);

        // Session silinmiş olabilir (oda kapatıldıysa) — sessizce çık
        if (! $session) {
            return;
        }

        // STALE CHECK: Bu job 45sn önce zamanlandı ama session başka bir
        // round'a geçmiş ya da faz değişmiş olabilir (erken resolve tetiklendiyse).
        // Her iki kontrol de gerekli — round uyuşsa bile faz 'night' değilse işlem yapma.
        if ($session->round_number !== $this->roundNumber) {
            return;
        }

        if ($session->phase !== 'night') {
            return;
        }

        // resolveNight() kendi içinde de refresh + faz kontrolü yapıyor (race condition koruması)
        $gameEngine->resolveNight($session);
    }
}
