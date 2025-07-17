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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChampionsLeagueApiController extends Controller
{
    protected LeagueService $leagueService;

    public function __construct(LeagueService $leagueService)
    {
        $this->leagueService = $leagueService;
    }

    /**
     * Güncel lig tablosu
     */
    public function getStandings(): AnonymousResourceCollection
    {
        $standings = $this->leagueService->getCurrentStandings();

        return LeagueStandingResource::collection($standings);
    }

    /**
     * Haftalık maçlar
     */
    public function getMatchesByWeek(int $week): AnonymousResourceCollection
    {
        $matches = GameMatch::where('week', $week)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        return GameMatchResource::collection($matches);
    }

    /**
     * Tüm maçlar
     */
    public function getAllMatches(): AnonymousResourceCollection
    {
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        return GameMatchResource::collection($matches);
    }

    /**
     * Tüm takımlar
     */
    public function getTeams(): AnonymousResourceCollection
    {
        $teams = Team::all();

        return TeamResource::collection($teams);
    }

    /**
     * Belirli bir maç detayı
     */
    public function getMatch(GameMatch $match): GameMatchResource
    {
        $match->load(['homeTeam', 'awayTeam']);

        return GameMatchResource::make($match);
    }

    /**
     * Haftalara göre gruplandırılmış maçlar
     */
    public function getMatchesByWeekGrouped()
    {
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        return $matches->groupBy('week')->map(function ($weekMatches) {
            return GameMatchResource::collection($weekMatches);
        });
    }
}
