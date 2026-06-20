<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameSessionResource;
use App\Http\Resources\RoomResource;
use App\Http\Resources\UserResource;
use App\Models\ChatMessage;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * GET /api/rooms?status=waiting
     * Açık (veya verilen statüdeki) odaların listesi.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'waiting');

        $query = Room::query()->with('owner');

        if ($status) {
            $query->where('status', $status);
        }

        $rooms = $query->latest('id')->paginate(20);

        return RoomResource::collection($rooms);
    }

    /**
     * POST /api/rooms
     * { name, max_players } -> oda oluştur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'max_players' => ['required', 'integer', Rule::in([4, 5, 6, 7, 8, 9, 10, 12, 15, 20, 25])],
        ]);

        $user = $request->user();

        $room = DB::transaction(function () use ($validated, $user) {
            $room = Room::create([
                'code' => Room::generateCode(),
                'name' => $validated['name'],
                'owner_id' => $user->id,
                'max_players' => $validated['max_players'],
                'current_players' => 1,
                'status' => 'waiting',
                'settings' => [],
            ]);

            // Kurucu kullanıcıyı otomatik olarak room_players pivot'una ekle.
            $room->players()->attach($user->id, [
                'joined_at' => now(),
            ]);

            return $room;
        });

        $room->load('owner');

        return (new RoomResource($room))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/rooms/{room}
     * Oda detayı + varsa aktif session.
     */
    public function show(Room $room)
    {
        $room->load(['owner', 'activeSession']);

        return new RoomResource($room);
    }

    /**
     * POST /api/rooms/{code}/join
     * Kod ile odaya katıl.
     */
    public function joinByCode(Request $request, string $code)
    {
        $room = Room::where('code', strtoupper($code))->first();

        if (! $room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        if ($room->status !== 'waiting') {
            return response()->json(['message' => 'Room is not joinable'], 409);
        }

        $user = $request->user();

        $alreadyJoined = $room->players()->where('user_id', $user->id)->exists();

        if (! $alreadyJoined && $room->current_players >= $room->max_players) {
            return response()->json(['message' => 'Room is full'], 409);
        }

        if (! $alreadyJoined) {
            DB::transaction(function () use ($room, $user) {
                $room->players()->attach($user->id, [
                    'joined_at' => now(),
                ]);

                $room->increment('current_players');
            });
        }

        $room->refresh()->load('owner');

        return new RoomResource($room);
    }

    /**
     * POST /api/rooms/{room}/start
     * Sadece owner çağırabilir.
     */
    public function startGame(Request $request, Room $room)
    {
        if ($room->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Only the room owner can start the game'], 403);
        }

        if ($room->status !== 'waiting') {
            return response()->json(['message' => 'Room is not in a startable state'], 409);
        }

        if ($room->current_players < 4) {
            return response()->json(['message' => 'Need at least 4 players'], 422);
        }

        $session = DB::transaction(function () use ($room) {
            $session = GameSession::create([
                'room_id' => $room->id,
                'round_number' => 1,
                'phase' => 'role_assignment',
                'night_actions_submitted' => false,
                'player_count' => $room->current_players,
                'started_at' => now(),
            ]);

            $room->update(['status' => 'playing']);

            // TODO: GameEngine::assignRoles($session)

            return $session;
        });

        return new GameSessionResource($session);
    }

    /**
     * POST /api/rooms/{room}/close
     * Sadece owner çağırabilir.
     */
    public function closeRoom(Request $request, Room $room)
    {
        if ($room->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'Only the room owner can close the room'], 403);
        }

        DB::transaction(function () use ($room) {
            $room->update(['status' => 'closed']);

            $activeSession = $room->activeSession;

            if ($activeSession) {
                $activeSession->update([
                    'phase' => 'finished',
                    'ended_at' => now(),
                ]);
            }
        });

        return response()->json(['message' => 'Room closed']);
    }

    /**
     * POST /api/rooms/{room}/leave
     */
    public function leaveRoom(Request $request, Room $room)
    {
        $user = $request->user();

        $isMember = $room->players()->where('user_id', $user->id)->exists();

        if (! $isMember) {
            return response()->json(['message' => 'You are not in this room'], 422);
        }

        DB::transaction(function () use ($room, $user) {
            $room->players()->detach($user->id);
            $room->decrement('current_players');

            if ($room->owner_id === $user->id) {
                // Basitlik için: owner ayrılırsa oda otomatik kapatılır.
                // Alternatif (ileride): kalan en eski katılan oyuncuyu yeni owner yapmak.
                $room->update(['status' => 'closed']);
            }
        });

        return response()->json(['message' => 'Left the room']);
    }

    /**
     * GET /api/rooms/{room}/session
     * Odanın aktif (bitmemiş) session'ı.
     */
    public function getSession(Room $room)
    {
        $session = $room->activeSession;

        if (! $session) {
            return response()->json(['message' => 'No active session'], 404);
        }

        return new GameSessionResource($session);
    }

    /**
     * GET /api/sessions/{session}/players
     */
    public function getPlayers(GameSession $session)
    {
        $players = $session->players()->with('user')->get();

        return $players->map(fn (GamePlayer $player) => [
            'id' => $player->id,
            'user' => new UserResource($player->user),
            'role' => $player->role,
            'is_alive' => $player->is_alive,
            'is_winner' => $player->is_winner,
        ]);
    }

    /**
     * GET /api/rooms/{room}/messages
     * Son 100 mesaj, eskiden yeniye sıralı. ?type= ile filtrelenebilir.
     */
    public function getMessages(Request $request, Room $room)
    {
        $query = $room->messages()->with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->take(100)
            ->get()
            ->sortBy('created_at')
            ->values();

        return $messages->map(fn (ChatMessage $message) => $this->formatMessage($message));
    }

    /**
     * POST /api/rooms/{room}/messages
     * { message } -> mesaj gönder. Kullanıcı odada olmalı.
     */
    public function postMessage(Request $request, Room $room)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $isMember = $room->players()->where('user_id', $user->id)->exists();

        if (! $isMember) {
            return response()->json(['message' => 'You are not in this room'], 403);
        }

        $activeSession = $room->activeSession;

        $message = ChatMessage::create([
            'room_id' => $room->id,
            'session_id' => $activeSession?->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'type' => $activeSession ? 'day' : 'lobby',
            'round_number' => $activeSession?->round_number,
        ]);

        $message->load('user');

        // TODO: broadcast(new ChatMessageSent($message))

        return response()->json($this->formatMessage($message), 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMessage(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'room_id' => $message->room_id,
            'session_id' => $message->session_id,
            'user' => $message->user ? new UserResource($message->user) : null,
            'message' => $message->message,
            'type' => $message->type,
            'round_number' => $message->round_number,
            'created_at' => $message->created_at,
        ];
    }
}
