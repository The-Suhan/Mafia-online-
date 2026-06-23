<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ---------------------------------------------------------------------------
// RoleAssigned — Her oyuncuya kendi private kanalından gönderilir.
// ---------------------------------------------------------------------------
class RoleAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $userId,
        public readonly string $role,
        /** @var string[] */
        public readonly array  $mafiaTeam = [],
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("player.{$this->userId}")];
    }

    public function broadcastAs(): string
    {
        return 'role.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'role'        => $this->role,
            'mafia_team'  => $this->mafiaTeam,
        ];
    }
}

// ---------------------------------------------------------------------------
// PhaseChanged — Odadaki herkese gönderilir.
// ---------------------------------------------------------------------------
class PhaseChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $sessionId,
        public readonly string $phase,
        public readonly int    $round,
        public readonly mixed  $phaseEndsAt,  // Carbon|null
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("session.{$this->sessionId}")];
    }

    public function broadcastAs(): string
    {
        return 'phase.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id'    => $this->sessionId,
            'phase'         => $this->phase,
            'round'         => $this->round,
            'phase_ends_at' => $this->phaseEndsAt,
        ];
    }
}

// ---------------------------------------------------------------------------
// PlayerEliminated — Odadaki herkese gönderilir (rol açıklanır).
// ---------------------------------------------------------------------------
class PlayerEliminated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $sessionId,
        public readonly int    $userId,
        public readonly string $role,
        public readonly string $reason,  // 'night_kill' | 'day_vote'
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("session.{$this->sessionId}")];
    }

    public function broadcastAs(): string
    {
        return 'player.eliminated';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'role'    => $this->role,
            'reason'  => $this->reason,
        ];
    }
}

// ---------------------------------------------------------------------------
// SheriffResult — Sadece komisere private kanalından gönderilir.
// ---------------------------------------------------------------------------
class SheriffResult implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $sheriffUserId,
        public readonly int    $targetUserId,
        public readonly string $result,   // 'mafia' | 'not_mafia'
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("player.{$this->sheriffUserId}")];
    }

    public function broadcastAs(): string
    {
        return 'sheriff.result';
    }

    public function broadcastWith(): array
    {
        return [
            'target_user_id' => $this->targetUserId,
            'result'         => $this->result,
        ];
    }
}

// ---------------------------------------------------------------------------
// VoteUpdated — Oy güncellemesini herkese yayınlar (opsiyonel).
// ---------------------------------------------------------------------------
class VoteUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $sessionId,
        public readonly int $targetUserId,
        public readonly int $voteCount,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("session.{$this->sessionId}")];
    }

    public function broadcastAs(): string
    {
        return 'vote.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'target_user_id' => $this->targetUserId,
            'vote_count'     => $this->voteCount,
        ];
    }
}

// ---------------------------------------------------------------------------
// GameEnded — Oyunun bitişini ve sonuçları herkese bildirir.
// ---------------------------------------------------------------------------
class GameEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $sessionId,
        public readonly string $winner,    // 'mafia' | 'villagers'
        /** @var array<int, array{user_id:int,nickname:string|null,role:string,is_winner:bool,xp_earned:int}> */
        public readonly array  $players,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel("session.{$this->sessionId}")];
    }

    public function broadcastAs(): string
    {
        return 'game.ended';
    }

    public function broadcastWith(): array
    {
        return [
            'winner'  => $this->winner,
            'players' => $this->players,
        ];
    }
}
