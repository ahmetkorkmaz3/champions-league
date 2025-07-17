<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PlayWeekRequest;
use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Services\LeagueService;
use Illuminate\Http\Response;

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
    public function playWeek(PlayWeekRequest $request): Response
    {
        $week = $request->validated('week');
        $this->leagueService->playWeek($week);

        return response()->noContent();
    }
}
