<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlayWeekRequest;
use App\Http\Resources\GameMatchResource;
use App\Http\Resources\LeagueStandingResource;
use App\Models\GameMatch;
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
        $this->leagueService->resetMatches();

        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $this->leagueService->getAllMatchesGroupedByWeek();
        $standings = $this->leagueService->getCurrentStandings();

        return response()->json([
            'matches' => GameMatchResource::collection($matches),
            'matchesByWeek' => $matchesByWeek->map(function ($weekMatches) {
                return GameMatchResource::collection($weekMatches);
            }),
            'standings' => LeagueStandingResource::collection($standings),
        ]);
    }

    /**
     * Play all matches
     */
    public function playAllMatches(): JsonResponse
    {
        $this->leagueService->playAllMatches();

        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $this->leagueService->getAllMatchesGroupedByWeek();
        $standings = $this->leagueService->getCurrentStandings();

        return response()->json([
            'matches' => GameMatchResource::collection($matches),
            'matchesByWeek' => $matchesByWeek->map(function ($weekMatches) {
                return GameMatchResource::collection($weekMatches);
            }),
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

        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $this->leagueService->getAllMatchesGroupedByWeek();
        $standings = $this->leagueService->getCurrentStandings();

        return response()->json([
            'matches' => GameMatchResource::collection($matches),
            'matchesByWeek' => $matchesByWeek->map(function ($weekMatches) {
                return GameMatchResource::collection($weekMatches);
            }),
            'standings' => LeagueStandingResource::collection($standings),
        ]);
    }
}
