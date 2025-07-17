<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreGameMatchRequest;
use App\Http\Requests\Api\UpdateGameMatchRequest;
use App\Http\Resources\GameMatchResource;
use App\Models\GameMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GameMatchController extends Controller
{
    /**
     * Display a listing of matches
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->when($request->has('week'), function ($query) use ($request) {
                $query->where('week', $request->week);
            })
            ->when($request->has('played'), function ($query) use ($request) {
                $query->where('is_played', $request->boolean('played'));
            })
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        return GameMatchResource::collection($matches);
    }

    /**
     * Store a newly created match
     */
    public function store(StoreGameMatchRequest $request): Response
    {
        GameMatch::create($request->validated());

        return response()->noContent(201);
    }

    /**
     * Display the specified match
     */
    public function show(GameMatch $match): GameMatchResource
    {
        $match->load(['homeTeam', 'awayTeam']);

        return GameMatchResource::make($match);
    }

    /**
     * Update the specified match
     */
    public function update(UpdateGameMatchRequest $request, GameMatch $match): JsonResponse
    {
        $match->update($request->validated());

        // Return updated data
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $matches->groupBy('week')->map(function ($weekMatches) {
            return GameMatchResource::collection($weekMatches);
        });

        $standings = \App\Models\LeagueStanding::with('team')
            ->orderBy('position', 'asc')
            ->get();

        return response()->json([
            'matches' => GameMatchResource::collection($matches),
            'matchesByWeek' => $matchesByWeek,
            'standings' => \App\Http\Resources\LeagueStandingResource::collection($standings),
        ]);
    }

    /**
     * Remove the specified match
     */
    public function destroy(GameMatch $match): Response
    {
        $match->delete();

        return response()->noContent();
    }

    /**
     * Get matches grouped by week
     */
    public function byWeek(): JsonResponse
    {
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $matches->groupBy('week')->map(function ($weekMatches) {
            return GameMatchResource::collection($weekMatches);
        });

        return response()->json([
            'data' => $matchesByWeek,
        ]);
    }
}
