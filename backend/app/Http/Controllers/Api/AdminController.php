<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameLog;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Bir kullanıcının "online" sayılması için son aktif token kullanım
     * eşiği (dakika). personal_access_tokens.last_used_at bu eşik
     * içindeyse kullanıcı online kabul edilir.
     */
    private const ONLINE_THRESHOLD_MINUTES = 5;

    // ─────────────────────────────────────────────
    //  GET /api/admin/stats
    // ─────────────────────────────────────────────

    public function stats(Request $request): JsonResponse
    {
        $onlinePlayers = DB::table('personal_access_tokens')
            ->where('last_used_at', '>=', now()->subMinutes(self::ONLINE_THRESHOLD_MINUTES))
            ->distinct('tokenable_id')
            ->count('tokenable_id');

        return response()->json([
            'total_users'    => User::count(),
            'active_rooms'   => Room::where('status', 'playing')->count(),
            'games_today'    => GameSession::whereDate('started_at', today())->count(),
            'online_players' => $onlinePlayers,

            // Basit placeholder'lar — gerçek trend hesaplaması opsiyonel
            'users_delta'    => '+0%',
            'rooms_delta'    => '+0%',
            'games_delta'    => '+0%',
            'online_delta'   => '+0%',
            'users_trend'    => 'up',
            'rooms_trend'    => 'up',
            'games_trend'    => 'up',
            'online_trend'   => 'up',
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/admin/activity?limit=10
    // ─────────────────────────────────────────────

    public function activity(Request $request): JsonResponse
    {
        $limit = $request->integer('limit', 10);

        $registrations = User::latest()->take($limit)->get()->map(fn ($u) => [
            'id'        => 'user_'.$u->id,
            'type'      => 'user_registered',
            'nickname'  => $u->nickname,
            'timestamp' => $u->created_at,
        ]);

        $gamesStarted = GameSession::with('room')
            ->whereNotNull('started_at')
            ->latest('started_at')
            ->take($limit)
            ->get()
            ->map(fn ($s) => [
                'id'        => 'game_start_'.$s->id,
                'type'      => 'game_started',
                'room_name' => $s->room->name ?? 'Unknown',
                'timestamp' => $s->started_at,
            ]);

        $gamesEnded = GameSession::with('room')
            ->whereNotNull('ended_at')
            ->latest('ended_at')
            ->take($limit)
            ->get()
            ->map(fn ($s) => [
                'id'        => 'game_end_'.$s->id,
                'type'      => 'game_ended',
                'room_name' => $s->room->name ?? 'Unknown',
                'winner'    => $s->winner,
                'timestamp' => $s->ended_at,
            ]);

        $roomsCreated = Room::with('owner')
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn ($r) => [
                'id'        => 'room_'.$r->id,
                'type'      => 'room_created',
                'room_name' => $r->name,
                'nickname'  => $r->owner->nickname ?? 'Unknown',
                'timestamp' => $r->created_at,
            ]);

        $activity = $registrations
            ->concat($gamesStarted)
            ->concat($gamesEnded)
            ->concat($roomsCreated)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();

        return response()->json($activity);
    }

    // ─────────────────────────────────────────────
    //  GET /api/admin/users?page=&search=&rank=
    // ─────────────────────────────────────────────

    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q
                ->where('nickname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($rank = $request->input('rank')) {
            $query->where('rank', strtolower($rank));
        }

        $perPage = 20;
        $page = $request->integer('page', 1);
        $paginated = $query->orderByDesc('created_at')->paginate($perPage, ['*'], 'page', $page);

        $userIds = $paginated->getCollection()->pluck('id');
        $onlineIds = DB::table('personal_access_tokens')
            ->whereIn('tokenable_id', $userIds)
            ->where('last_used_at', '>=', now()->subMinutes(self::ONLINE_THRESHOLD_MINUTES))
            ->distinct()
            ->pluck('tokenable_id')
            ->all();

        $users = $paginated->getCollection()->map(function ($u) use ($onlineIds) {
            return [
                'id'          => $u->id,
                'nickname'    => $u->nickname,
                'email'       => $u->email,
                'avatar_url'  => $u->avatar,
                'rank'        => ucfirst($u->rank),
                'xp'          => $u->xp,
                'total_games' => $u->total_played,
                'wins'        => $u->total_wins,
                'status'      => in_array($u->id, $onlineIds) ? 'Online' : 'Offline',
                'joined_at'   => $u->created_at,
                'is_admin'    => $u->is_admin,
            ];
        });

        return response()->json([
            'items' => $users,
            'total' => $paginated->total(),
            'page'  => $paginated->currentPage(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/admin/users/{id}
    // ─────────────────────────────────────────────

    public function userDetail(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $isOnline = $this->isUserOnline($user->id);

        $lastActiveAt = DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->orderByDesc('last_used_at')
            ->value('last_used_at');

        return response()->json([
            'id'                => $user->id,
            'nickname'          => $user->nickname,
            'email'             => $user->email,
            'avatar_url'        => $user->avatar,
            'rank'              => ucfirst($user->rank),
            'level'             => intdiv($user->xp, 500) + 1,
            'xp'                => $user->xp,
            'xp_to_next_level'  => User::nextRankThreshold($user->xp),
            'total_games'       => $user->total_played,
            'wins'              => $user->total_wins,
            'losses'            => $user->total_losses,
            'is_admin'          => $user->is_admin,
            'is_online'         => $isOnline,
            'created_at'        => $user->created_at,
            'last_active_at'    => $lastActiveAt,
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/admin/users/{id}/games?limit=10
    // ─────────────────────────────────────────────

    public function userGames(Request $request, int $id): JsonResponse
    {
        $games = GamePlayer::with('session.room')
            ->where('user_id', $id)
            ->latest()
            ->take($request->integer('limit', 10))
            ->get()
            ->map(fn ($gp) => [
                'session_id' => $gp->session_id,
                'room_name'  => $gp->session->room->name ?? 'Unknown',
                'role'       => ucfirst($gp->role),
                'result'     => $gp->is_winner ? 'Win' : 'Loss',
                'date'       => $gp->created_at,
            ]);

        return response()->json($games);
    }

    // ─────────────────────────────────────────────
    //  DELETE /api/admin/users/{id}
    // ─────────────────────────────────────────────

    public function deleteUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Kendi admin hesabını silmeyi engelle
        if ($request->user() && $request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($user) {
                // Önce mevcut token'larını iptal et, sonra kullanıcıyı sil
                $user->tokens()->delete();
                $user->delete();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Foreign key constraint hatası (örn. GamePlayer, Room.owner_id
            // gibi ilişkili kayıtlar cascade/nullable değilse) — 500 yerine
            // anlamlı bir hata döndür. İlişkili tabloların gerçek davranışı
            // (cascade/set null/restrict) ilgili migration'lara bağlıdır;
            // burada varsayım yapılmıyor.
            if ((int) $e->getCode() === 23000) {
                return response()->json([
                    'message' => 'Bu kullanıcı silinemiyor çünkü ilişkili oyun/oda kayıtları var. '
                        .'Önce bu kayıtların nasıl ele alınacağına (cascade, arşivleme vb.) karar verilmeli.',
                ], 409);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'User deleted',
        ]);
    }

    // ─────────────────────────────────────────────
    //  PATCH /api/admin/users/{id}/xp   body: { xp }
    // ─────────────────────────────────────────────

    public function updateXp(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'xp' => 'required|integer|min:0',
        ]);

        $user = User::findOrFail($id);
        $user->xp = $request->integer('xp');
        $user->rank = User::calculateRank($user->xp);
        $user->save();

        return response()->json([
            'xp'   => $user->xp,
            'rank' => $user->rank,
        ]);
    }

    // ─────────────────────────────────────────────
    //  PATCH /api/admin/users/{id}/role  body: { is_admin }
    // ─────────────────────────────────────────────

    public function updateRole(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'is_admin' => 'required|boolean',
        ]);

        $user = User::findOrFail($id);
        $user->is_admin = $request->boolean('is_admin');
        $user->save();

        return response()->json([
            'is_admin' => $user->is_admin,
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/admin/sessions?page=&status=&date_from=&date_to=
    // ─────────────────────────────────────────────

    public function sessions(Request $request): JsonResponse
    {
        $query = GameSession::with('room');

        if (($status = $request->input('status')) && $status !== 'all') {
            if ($status === 'finished') {
                $query->whereNotNull('ended_at');
            }
            if ($status === 'in_progress') {
                $query->whereNull('ended_at');
            }
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('started_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('started_at', '<=', $to);
        }

        $paginated = $query->orderByDesc('started_at')->paginate(20);

        $items = $paginated->getCollection()->map(fn ($s) => [
            'id'                => $s->id,
            'room_name'         => $s->room->name ?? 'Unknown',
            'player_count'      => $s->player_count,
            'max_players'       => $s->room->max_players ?? null,
            'winner'            => $s->winner,
            'status'            => $s->ended_at ? 'finished' : 'in_progress',
            'duration_seconds'  => $s->ended_at && $s->started_at
                ? $s->started_at->diffInSeconds($s->ended_at)
                : null,
            'started_at'        => $s->started_at,
            'ended_at'          => $s->ended_at,
        ]);

        return response()->json([
            'items' => $items,
            'total' => $paginated->total(),
            'page'  => $paginated->currentPage(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/admin/sessions/{id}
    // ─────────────────────────────────────────────

    public function sessionDetail(Request $request, int $id): JsonResponse
    {
        $session = GameSession::with(['room', 'players.user'])->findOrFail($id);

        $players = $session->players->map(fn ($p) => [
            'id'       => $p->id,
            'nickname' => $p->user->nickname ?? 'Unknown',
            'role'     => $p->role,
            'status'   => $p->is_alive ? 'Alive' : 'Eliminated',
            'xp'       => $p->xp_earned,
            'result'   => $p->is_winner ? 'WIN' : 'LOSS',
        ]);

        return response()->json([
            'id'           => $session->id,
            'roomName'     => $session->room->name ?? 'Unknown',
            'winner'       => strtoupper($session->winner ?? ''),
            'totalPlayers' => $session->player_count,
            'totalRounds'  => $session->round_number,
            'duration'     => $session->ended_at && $session->started_at
                ? $session->started_at->diff($session->ended_at)->format('%im %ss')
                : '—',
            'startedAt' => $session->started_at,
            'endedAt'   => $session->ended_at,
            'players'   => $players,
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /api/admin/sessions/{id}/logs
    // ─────────────────────────────────────────────

    public function sessionLogs(Request $request, int $id): JsonResponse
    {
        $logs = GameLog::where('session_id', $id)
            ->orderBy('round_number')
            ->orderBy('created_at')
            ->get()
            ->map(function ($log) {
                $phase = str_contains($log->event, 'night')
                    || in_array($log->event, ['player_killed', 'player_saved', 'player_inspected'])
                    ? 'night'
                    : 'day';

                return [
                    'id'        => $log->id,
                    'round'     => $log->round_number,
                    'phase'     => $phase,
                    'eventType' => match ($log->event) {
                        'player_killed'      => 'kill',
                        'player_saved'       => 'save',
                        'player_inspected'   => 'investigate',
                        'player_eliminated'  => 'vote_elimination',
                        default               => $log->event,
                    },
                    'message' => $this->formatLogMessage($log),
                ];
            });

        return response()->json($logs);
    }

    // ─────────────────────────────────────────────
    //  Yardımcı metotlar
    // ─────────────────────────────────────────────

    /**
     * Verilen kullanıcı ID'sinin son ONLINE_THRESHOLD_MINUTES içinde
     * aktif bir token kullanıp kullanmadığını kontrol eder.
     */
    private function isUserOnline(int $userId): bool
    {
        return DB::table('personal_access_tokens')
            ->where('tokenable_id', $userId)
            ->where('last_used_at', '>=', now()->subMinutes(self::ONLINE_THRESHOLD_MINUTES))
            ->exists();
    }

    /**
     * GameLog kaydının `data` (JSON) alanından okunabilir bir mesaj üretir.
     *
     * NOT: `data` kolonunun kesin şekli GameEngine'deki GameLog::create()
     * çağrılarına bağlıdır. Burada yaygın alan adları (`target`,
     * `target_nickname`, `actor`, `role`, vb.) için fallback'li bir
     * eşleme yapılmıştır. Gerçek alan adları farklıysa burası
     * güncellenmelidir.
     */
    private function formatLogMessage(GameLog $log): string
    {
        $data = is_array($log->data)
            ? $log->data
            : (json_decode((string) $log->data, true) ?? []);

        $target = $data['target_nickname']
            ?? $data['target']
            ?? $data['nickname']
            ?? 'Bir oyuncu';

        $extraRole = $data['revealed_role'] ?? $data['role'] ?? null;

        return match ($log->event) {
            'player_killed' => "{$target} öldürüldü.",
            'player_saved' => "{$target} kurtarıldı.",
            'player_inspected' => $extraRole
                ? "{$target} incelendi ({$extraRole} çıktı)."
                : "{$target} incelendi.",
            'player_eliminated' => $extraRole
                ? "{$target} elendi ({$extraRole} çıktı)."
                : "{$target} elendi.",
            default => trim(ucfirst(str_replace('_', ' ', $log->event)).": {$target}"),
        };
    }
}
