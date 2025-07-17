<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlayWeekRequest;
use App\Http\Resources\GameMatchResource;
use App\Http\Resources\LeagueStandingResource;
use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Services\LeagueService;
use Illuminate\Http\JsonResponse;

class ChampionsLeagueApiController extends Controller
{
    protected LeagueService $leagueService;

    public function __construct(LeagueService $leagueService)
    {
        $this->leagueService = $leagueService;
    }

    /**
     * Reset all matches
     */
    public function resetMatches(): JsonResponse
    {
        // Reset all matches
        GameMatch::query()->update([
            'home_score' => null,
            'away_score' => null,
            'is_played' => false,
        ]);

        // Clear standings
        LeagueStanding::query()->update([
            'points' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'position' => 0,
        ]);

        // Return updated data
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $matches->groupBy('week')->map(function ($weekMatches) {
            return GameMatchResource::collection($weekMatches);
        });

        $standings = LeagueStanding::with('team')
            ->orderBy('position')
            ->get();

        return response()->json([
            'matches' => GameMatchResource::collection($matches),
            'matchesByWeek' => $matchesByWeek,
            'standings' => LeagueStandingResource::collection($standings),
        ]);
    }

    /**
     * Play all matches
     */
    public function playAllMatches(): JsonResponse
    {
        $this->leagueService->playAllMatches();

        // Return updated data
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $matches->groupBy('week')->map(function ($weekMatches) {
            return GameMatchResource::collection($weekMatches);
        });

        $standings = LeagueStanding::with('team')
            ->orderBy('position', 'asc')
            ->get();

        return response()->json([
            'matches' => GameMatchResource::collection($matches),
            'matchesByWeek' => $matchesByWeek,
            'standings' => LeagueStandingResource::collection($standings),
        ]);
    }

    /**
     * Play matches for a specific week
     */
    public function playWeek(PlayWeekRequest $request): JsonResponse
    {
        $week = $request->validated('week');
        $this->leagueService->playWeek($week);

        // Return updated data
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $matches->groupBy('week')->map(function ($weekMatches) {
            return GameMatchResource::collection($weekMatches);
        });

        $standings = LeagueStanding::with('team')
            ->orderBy('position', 'asc')
            ->get();

        return response()->json([
            'matches' => GameMatchResource::collection($matches),
            'matchesByWeek' => $matchesByWeek,
            'standings' => LeagueStandingResource::collection($standings),
        ]);
    }
}
