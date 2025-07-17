<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameMatchResource;
use App\Http\Resources\LeagueStandingResource;
use App\Http\Resources\TeamResource;
use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Models\Team;
use App\Services\LeagueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChampionsLeagueApiController extends Controller
{
    protected LeagueService $leagueService;

    public function __construct(LeagueService $leagueService)
    {
        $this->leagueService = $leagueService;
    }

    /**
     * Display a listing of teams
     */
    public function teams(): AnonymousResourceCollection
    {
        $teams = Team::orderBy('name')->get();
        return TeamResource::collection($teams);
    }

    /**
     * Display a listing of matches
     */
    public function matches(Request $request): AnonymousResourceCollection
    {
        $query = GameMatch::with(['homeTeam', 'awayTeam']);

        // Filter by week if provided
        if ($request->has('week')) {
            $query->where('week', $request->week);
        }

        // Filter by played status if provided
        if ($request->has('played')) {
            $query->where('is_played', $request->boolean('played'));
        }

        $matches = $query->orderBy('week')->orderBy('id')->get();
        return GameMatchResource::collection($matches);
    }

    /**
     * Display matches grouped by week
     */
    public function matchesByWeek(): JsonResponse
    {
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $matchesByWeek = $matches->groupBy('week')->map(function ($weekMatches) {
            return GameMatchResource::collection($weekMatches);
        });

        return response()->json([
            'data' => $matchesByWeek
        ]);
    }

    /**
     * Display the specified match
     */
    public function showMatch(GameMatch $match): GameMatchResource
    {
        $match->load(['homeTeam', 'awayTeam']);

        return GameMatchResource::make($match);
    }

    /**
     * Display current standings
     */
    public function standings(): AnonymousResourceCollection
    {
        $standings = $this->leagueService->getCurrentStandings();

        return LeagueStandingResource::collection($standings);
    }

    /**
     * Reset all matches
     */
    public function resetMatches(): Response
    {
        // Reset all matches
        GameMatch::query()->update([
            'home_score' => null,
            'away_score' => null,
            'is_played' => false,
        ]);

        // Clear standings
        LeagueStanding::query()->delete();

        return response()->noContent();
    }

    /**
     * Play all matches
     */
    public function playAllMatches(): Response
    {
        $this->leagueService->playAllMatches();
        
        return response()->noContent();
    }

    /**
     * Play matches for a specific week
     */
    public function playWeek(Request $request): Response
    {
        $request->validate([
            'week' => 'required|integer|min:1|max:10'
        ]);

        $week = $request->week;
        $this->leagueService->playWeek($week);

        return response()->noContent();
    }
}
