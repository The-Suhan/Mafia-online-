<?php

namespace App\Services;

use App\Events\GameEnded;
use App\Events\PhaseChanged;
use App\Events\PlayerEliminated;
use App\Events\RoleAssigned;
use App\Events\SheriffResult;
use App\Events\VoteUpdated;
use App\Jobs\DayPhaseStartJob;
use App\Jobs\NightResolutionJob; // NightResolutionJob ayrı task'ta oluşturulacak
use App\Models\ChatMessage;
use App\Models\DayVote;
use App\Models\GameLog;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\NightAction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameEngine
{
    // -------------------------------------------------------------------------
    // assignRoles
    // -------------------------------------------------------------------------

    public function assignRoles(GameSession $session): void
    {
        $session->load('room.players');

        $users = $session->room->players()->get();

        // Rol listesini oyuncu sayısına göre oluştur
        $distribution = XpService::getRoleDistribution($users->count());

        $roles = [];
        foreach ($distribution as $role => $count) {
            for ($i = 0; $i < $count; $i++) {
                $roles[] = $role;
            }
        }

        shuffle($roles);

        // Her kullanıcıya rol ata, GamePlayer kayıtlarını oluştur
        $gamePlayers = [];

        DB::transaction(function () use ($users, $roles, $session, &$gamePlayers) {
            foreach ($users as $index => $user) {
                $player = GamePlayer::create([
                    'session_id' => $session->id,
                    'user_id'    => $user->id,
                    'role'       => $roles[$index],
                    'is_alive'   => true,
                ]);
                $player->setRelation('user', $user);
                $gamePlayers[] = $player;
            }
        });

        // Mafia takımı nickname listesi (sadece mafia üyelerine gönderilecek)
        $mafiaTeamNicknames = collect($gamePlayers)
            ->filter(fn (GamePlayer $p) => $p->isMafia())
            ->map(fn (GamePlayer $p) => $p->user->nickname)
            ->values()
            ->all();

        // Her oyuncuya kendi rolünü broadcast et
        foreach ($gamePlayers as $player) {
            $teamInfo = $player->isMafia() ? $mafiaTeamNicknames : [];
            broadcast(new RoleAssigned($player->user_id, $player->role, $teamInfo));
        }

        // BOT mesajı: oyun başladı
        $this->sendBotMessage($session->room_id, $session->id, '🎭 Oyun başladı! Roller dağıtıldı. İlk gece başlıyor...');

        // Faz: night
        $session->update([
            'phase'         => 'night',
            'phase_ends_at' => now()->addSeconds(45),
        ]);

        broadcast(new PhaseChanged($session->id, 'night', $session->round_number, now()->addSeconds(45)));

        // 45 saniye sonra gece çözümlemesi
        NightResolutionJob::dispatch($session->id, $session->round_number)
            ->delay(now()->addSeconds(45));
    }

    // -------------------------------------------------------------------------
    // submitNightAction
    // -------------------------------------------------------------------------

    /**
     * @return array{submitted: bool, target_id: int}
     * @throws \InvalidArgumentException
     */
    public function submitNightAction(
        GameSession $session,
        User $actor,
        int $targetId,
        string $actionType
    ): array {
        // Actor bu session'da canlı bir oyuncu olmalı
        $actorPlayer = $session->players()
            ->where('user_id', $actor->id)
            ->where('is_alive', true)
            ->first();

        if (! $actorPlayer) {
            throw new \InvalidArgumentException('Actor is not an alive player in this session.');
        }

        // Rol-aksiyon uyumu kontrolü
        $allowedRoles = match ($actionType) {
            'kill'    => ['mafia', 'godfather'],
            'heal'    => ['doctor'],
            'inspect' => ['sheriff'],
            default   => throw new \InvalidArgumentException("Unknown action type: {$actionType}"),
        };

        if (! in_array($actorPlayer->role, $allowedRoles, true)) {
            throw new \InvalidArgumentException(
                "Role '{$actorPlayer->role}' cannot perform action '{$actionType}'."
            );
        }

        // Hedef canlı olmalı
        $targetAlive = $session->players()
            ->where('user_id', $targetId)
            ->where('is_alive', true)
            ->exists();

        if (! $targetAlive) {
            throw new \InvalidArgumentException('Target player is not alive.');
        }

        /*
         * Mafia kill aksiyonu için NOT:
         * Basitlik açısından "son gönderen kazanır" kuralı uygulanır.
         * Yani tüm mafia üyeleri kill aksiyonu gönderebilir; en son
         * gönderilen hedef, gece çözümlemesinde kullanılır.
         * Gelecekte godfather'ın tek yetkili olması istenirse bu metod
         * güncellenerek sadece godfather'ın kill aksiyonu kabul edilebilir.
         */

        NightAction::updateOrCreate(
            [
                'session_id'   => $session->id,
                'round_number' => $session->round_number,
                'actor_id'     => $actor->id,
            ],
            [
                'target_id'   => $targetId,
                'action_type' => $actionType,
                'is_resolved' => false,
            ]
        );

        // ── Erken Bitirme Kontrolü ─────────────────────────────────────────
        // Aksiyon gerektiren tüm rol TİPLERİ gönderdiyse, 45sn beklemeden çöz.
        // Mafia/godfather TEK bir kill action paylaştığından kişi sayısı değil
        // aksiyon tipi bazında kontrol yapıyoruz.
        $alivePlayers = $session->players()->where('is_alive', true)->get();

        $neededActionTypes = collect();

        if ($alivePlayers->whereIn('role', ['mafia', 'godfather'])->isNotEmpty()) {
            $neededActionTypes->push('kill');
        }
        if ($alivePlayers->where('role', 'doctor')->isNotEmpty()) {
            $neededActionTypes->push('heal');
        }
        if ($alivePlayers->where('role', 'sheriff')->isNotEmpty()) {
            $neededActionTypes->push('inspect');
        }

        $submittedActionTypes = NightAction::where('session_id', $session->id)
            ->where('round_number', $session->round_number)
            ->pluck('action_type')
            ->unique();

        if ($neededActionTypes->diff($submittedActionTypes)->isEmpty()) {
            // Herkes aksiyonunu gönderdi — 45sn beklemeden hemen çöz
            $this->resolveNight($session);
        }

        return ['submitted' => true, 'target_id' => $targetId];
    }

    // -------------------------------------------------------------------------
    // submitDayVote
    // -------------------------------------------------------------------------

    /**
     * @return array{submitted: bool, target_id: int}
     * @throws \InvalidArgumentException
     */
    public function submitDayVote(GameSession $session, User $voter, int $targetId): array
    {
        // Voter canlı olmalı
        $voterAlive = $session->players()
            ->where('user_id', $voter->id)
            ->where('is_alive', true)
            ->exists();

        if (! $voterAlive) {
            throw new \InvalidArgumentException('Voter is not an alive player in this session.');
        }

        // Hedef canlı olmalı
        $targetAlive = $session->players()
            ->where('user_id', $targetId)
            ->where('is_alive', true)
            ->exists();

        if (! $targetAlive) {
            throw new \InvalidArgumentException('Target player is not alive.');
        }

        DayVote::updateOrCreate(
            [
                'session_id'   => $session->id,
                'round_number' => $session->round_number,
                'voter_id'     => $voter->id,
            ],
            ['target_id' => $targetId]
        );

        // Anlık oy sayısını broadcast et (opsiyonel, frontend için)
        $voteCount = DayVote::where('session_id', $session->id)
            ->where('round_number', $session->round_number)
            ->where('target_id', $targetId)
            ->count();

        broadcast(new VoteUpdated($session->id, $targetId, $voteCount));

        return ['submitted' => true, 'target_id' => $targetId];
    }

    // -------------------------------------------------------------------------
    // resolveNight
    // -------------------------------------------------------------------------

    public function resolveNight(GameSession $session): void
    {
        // ── Race Condition Koruması ───────────────────────────────────────────
        // Hem erken-bitirme hem de NightResolutionJob aynı anda tetiklenebilir.
        // DB'den taze veriyi çek; faz artık 'night' değilse ikinci çağrıyı yok say.
        $session->refresh();
        if ($session->phase !== 'night') {
            return;
        }

        $round = $session->round_number;

        $actions = NightAction::where('session_id', $session->id)
            ->where('round_number', $round)
            ->get()
            ->keyBy('action_type');

        $killAction    = $actions->get('kill');
        $healAction    = $actions->get('heal');
        $inspectAction = $actions->get('inspect');

        $killTargetId = $killAction?->target_id;
        $healTargetId = $healAction?->target_id;

        // Ölüm / kurtarma mantığı
        $killedPlayerId = null;

        if ($killTargetId !== null) {
            if ($killTargetId === $healTargetId) {
                // Doktor kurtardı
                GameLog::create([
                    'session_id'   => $session->id,
                    'round_number' => $round,
                    'event'        => 'player_saved',
                    'data'         => ['victim_id' => $killTargetId],
                ]);

                $this->sendBotMessage(
                    $session->room_id,
                    $session->id,
                    '🛡️ Doktor birini kurtardı, bu gece kimse ölmedi.',
                    $round
                );
            } else {
                // Oyuncu öldü
                $victim = GamePlayer::where('session_id', $session->id)
                    ->where('user_id', $killTargetId)
                    ->first();

                if ($victim) {
                    $victim->update(['is_alive' => false]);
                    $killedPlayerId = $victim->id;

                    GameLog::create([
                        'session_id'   => $session->id,
                        'round_number' => $round,
                        'event'        => 'player_killed',
                        'data'         => [
                            'victim_id'       => $killTargetId,
                            'victim_nickname' => $victim->user->nickname ?? 'Unknown',
                        ],
                    ]);

                    $nickname = $victim->user->nickname ?? 'Bilinmeyen';
                    $this->sendBotMessage(
                        $session->room_id,
                        $session->id,
                        "🔪 {$nickname} bu gece öldürüldü.",
                        $round
                    );

                    broadcast(new PlayerEliminated(
                        $session->id,
                        $killTargetId,
                        $victim->role,
                        'night_kill'
                    ));
                }
            }
        } else {
            // Mafia hiç seçim yapmadı
            $this->sendBotMessage(
                $session->room_id,
                $session->id,
                '🌙 Sessiz bir gece geçti, kimse ölmedi.',
                $round
            );
        }

        // Inspect sonucu (sadece komisere)
        if ($inspectAction) {
            $inspectTarget = GamePlayer::where('session_id', $session->id)
                ->where('user_id', $inspectAction->target_id)
                ->first();

            if ($inspectTarget) {
                $result = $inspectTarget->isMafia() ? 'mafia' : 'not_mafia';

                // Sadece komiserin private kanalına gönder
                broadcast(new SheriffResult(
                    $inspectAction->actor_id,
                    $inspectAction->target_id,
                    $result
                ));
            }
        }

        // İşaretlenen aksiyonları resolved yap
        NightAction::where('session_id', $session->id)
            ->where('round_number', $round)
            ->update(['is_resolved' => true]);

        // Kazanma koşulunu kontrol et
        $winner = $this->checkWinCondition($session->refresh());
        if ($winner !== null) {
            $this->endGame($session, $winner);
            return;
        }

        // Gündüz duyuru fazına geç (8 saniyelik kısa faz)
        $session->update([
            'phase'         => 'day_announcement',
            'phase_ends_at' => now()->addSeconds(8),
        ]);

        broadcast(new PhaseChanged($session->id, 'day_announcement', $round, now()->addSeconds(8)));

        // 8 saniye sonra day_vote fazı
        DayPhaseStartJob::dispatch($session->id)->delay(now()->addSeconds(8));
    }

    // -------------------------------------------------------------------------
    // resolveDayVote
    // -------------------------------------------------------------------------

    public function resolveDayVote(GameSession $session): void
    {
        // ── Race Condition Koruması ───────────────────────────────────────────
        // DayVotePhaseEndJob ile manuel erken-bitirme aynı anda çalışabilir.
        $session->refresh();
        if ($session->phase !== 'day_vote') {
            return;
        }

        $round = $session->round_number;

        $votes = DayVote::where('session_id', $session->id)
            ->where('round_number', $round)
            ->selectRaw('target_id, COUNT(*) as vote_count')
            ->groupBy('target_id')
            ->orderByDesc('vote_count')
            ->get();

        if ($votes->isEmpty()) {
            // Hiç oy kullanılmadı, geceye geç
            $this->proceedToNextNight($session);
            return;
        }

        // Eşitlik varsa rastgele birini seç (alternatif: kimse elenmez — basitlik için rastgele)
        $maxVotes   = $votes->first()->vote_count;
        $topTargets = $votes->where('vote_count', $maxVotes)->pluck('target_id');
        $eliminatedUserId = $topTargets->count() > 1
            ? $topTargets->random()
            : $topTargets->first();

        /*
         * NOT: Eşitlik durumunda rastgele oyuncu elenir.
         * İleride "eşitlikte kimse elenmez" kuralı uygulanmak istenirse
         * $topTargets->count() > 1 kontrolünde return yapılabilir.
         */

        $eliminatedPlayer = GamePlayer::where('session_id', $session->id)
            ->where('user_id', $eliminatedUserId)
            ->first();

        if (! $eliminatedPlayer) {
            $this->proceedToNextNight($session);
            return;
        }

        $eliminatedPlayer->update(['is_alive' => false]);

        $nickname = $eliminatedPlayer->user->nickname ?? 'Bilinmeyen';
        $role     = $eliminatedPlayer->role;

        GameLog::create([
            'session_id'   => $session->id,
            'round_number' => $round,
            'event'        => 'player_eliminated',
            'data'         => [
                'user_id'  => $eliminatedUserId,
                'nickname' => $nickname,
                'role'     => $role,
            ],
        ]);

        $this->sendBotMessage(
            $session->room_id,
            $session->id,
            "⚖️ Köy oylaması sonucu: {$nickname} meydandan kovuldu ({$role} çıktı).",
            $round
        );

        broadcast(new PlayerEliminated(
            $session->id,
            $eliminatedUserId,
            $role,
            'day_vote'
        ));

        // Kazanma koşulu
        $winner = $this->checkWinCondition($session->refresh());
        if ($winner !== null) {
            $this->endGame($session, $winner);
            return;
        }

        $this->proceedToNextNight($session);
    }

    // -------------------------------------------------------------------------
    // checkWinCondition
    // -------------------------------------------------------------------------

    public function checkWinCondition(GameSession $session): ?string
    {
        $aliveMafia = $session->players()
            ->where('is_alive', true)
            ->whereIn('role', ['mafia', 'godfather', 'silencer'])
            ->count();

        $aliveVillagers = $session->players()
            ->where('is_alive', true)
            ->whereNotIn('role', ['mafia', 'godfather', 'silencer'])
            ->count();

        if ($aliveMafia === 0) {
            return 'villagers';
        }

        if ($aliveMafia >= $aliveVillagers) {
            return 'mafia';
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // endGame
    // -------------------------------------------------------------------------

    public function endGame(GameSession $session, string $winner): void
    {
        $session->update([
            'winner'   => $winner,
            'phase'    => 'finished',
            'ended_at' => now(),
        ]);

        // Oda "playing" olarak kalır — "tekrar oyna" özelliği için kapatılmaz

        $players = $session->players()->with('user')->get();

        $summary = [];

        foreach ($players as $player) {
            $isMafiaRole  = $player->isMafia();
            $isWinner     = ($isMafiaRole && $winner === 'mafia')
                || (! $isMafiaRole && $winner === 'villagers');

            // Basit bonus hesabı — gerçek veriye göre XpService::calculate çağrılabilir
            $bonuses = [
                'survived' => $player->is_alive,
            ];

            $xp = XpService::calculate($isWinner, $player->role, $bonuses);

            $player->update([
                'is_winner' => $isWinner,
                'xp_earned' => $xp,
            ]);

            if ($player->user) {
                $player->user->addXp($xp);
                $player->user->increment('total_played');

                if ($isWinner) {
                    $player->user->increment('total_wins');
                } else {
                    $player->user->increment('total_losses');
                }
            }

            $summary[] = [
                'user_id'   => $player->user_id,
                'nickname'  => $player->user?->nickname,
                'role'      => $player->role,
                'is_winner' => $isWinner,
                'xp_earned' => $xp,
            ];
        }

        $winnerLabel = $winner === 'mafia' ? 'Mafia' : 'Siviller';
        $this->sendBotMessage(
            $session->room_id,
            $session->id,
            "🏆 {$winnerLabel} kazandı! Oyun sona erdi."
        );

        broadcast(new GameEnded($session->id, $winner, $summary));
    }

    // -------------------------------------------------------------------------
    // Yardımcı metodlar
    // -------------------------------------------------------------------------

    private function proceedToNextNight(GameSession $session): void
    {
        $newRound = $session->round_number + 1;

        $session->update([
            'round_number'  => $newRound,
            'phase'         => 'night',
            'phase_ends_at' => now()->addSeconds(45),
        ]);

        broadcast(new PhaseChanged($session->id, 'night', $newRound, now()->addSeconds(45)));

        NightResolutionJob::dispatch($session->id, $newRound)
            ->delay(now()->addSeconds(45));
    }

    private function sendBotMessage(
        int $roomId,
        int $sessionId,
        string $message,
        ?int $roundNumber = null
    ): void {
        $chatMessage = ChatMessage::create([
            'room_id'      => $roomId,
            'session_id'   => $sessionId,
            'user_id'      => null,
            'message'      => $message,
            'type'         => 'bot',
            'round_number' => $roundNumber,
        ]);

        // TODO: broadcast(new ChatMessageSent($chatMessage))
    }
}
