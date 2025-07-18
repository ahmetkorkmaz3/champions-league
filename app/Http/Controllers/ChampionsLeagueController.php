<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Team;
use App\Services\LeagueService;
use App\Services\MatchService;
use Inertia\Inertia;
use Inertia\Response;

class ChampionsLeagueController extends Controller
{
    protected MatchService $matchService;

    protected LeagueService $leagueService;

    public function __construct(MatchService $matchService, LeagueService $leagueService)
    {
        $this->matchService = $matchService;
        $this->leagueService = $leagueService;
    }

    /**
     * Ana sayfa - Lig tablosu ve maçlar
     */
    public function index(): Response
    {
        $teams = Team::all();
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        $standings = $this->leagueService->getCurrentStandings();

        // Haftalara göre maçları grupla
        $matchesByWeek = $matches->groupBy('week');

        return Inertia::render('ChampionsLeague/Index', [
            'teams' => $teams,
            'matches' => $matches,
            'matchesByWeek' => $matchesByWeek,
            'standings' => $standings,
        ]);
    }
}
