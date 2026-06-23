<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use App\Services\GameEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function __construct(private readonly GameEngine $engine) {}

    /**
     * POST /api/game/{session}/night-action
     * body: { action_type, target_id }
     */
    public function nightAction(Request $request, GameSession $session): JsonResponse
    {
        $validated = $request->validate([
            'action_type' => ['required', 'string', 'in:kill,heal,inspect'],
            'target_id'   => ['required', 'integer'],
        ]);

        if ($session->phase !== 'night') {
            return response()->json(['message' => 'Not in night phase'], 409);
        }

        try {
            $result = $this->engine->submitNightAction(
                $session,
                $request->user(),
                $validated['target_id'],
                $validated['action_type'],
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    /**
     * POST /api/game/{session}/vote
     * body: { target_id }
     */
    public function vote(Request $request, GameSession $session): JsonResponse
    {
        $validated = $request->validate([
            'target_id' => ['required', 'integer'],
        ]);

        if ($session->phase !== 'day_vote') {
            return response()->json(['message' => 'Not in day vote phase'], 409);
        }

        try {
            $result = $this->engine->submitDayVote(
                $session,
                $request->user(),
                $validated['target_id'],
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }
}
