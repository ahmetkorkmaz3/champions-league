<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMatchRequest;
use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Models\Team;
use App\Services\LeagueService;
use App\Services\MatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /**
     * Belirli bir haftayı oynat
     */
    public function playWeek(Request $request): RedirectResponse
    {
        $week = $request->input('week');

        $this->matchService->playWeek($week);
        $this->leagueService->updateStandings();

        return redirect()->back()->with('success', "{$week}. hafta maçları oynandı!");
    }

    /**
     * Tüm maçları oynat
     */
    public function playAllMatches(): RedirectResponse
    {
        $this->matchService->playAllMatches();
        $this->leagueService->updateStandings();

        return redirect()->back()->with('success', 'Tüm maçlar oynandı!');
    }

    /**
     * Tahmin sayfası
     */
    public function predictions(): Response
    {
        $currentWeek = GameMatch::where('is_played', true)->max('week') ?? 0;
        $predictedStandings = $this->leagueService->getPredictedStandings($currentWeek);

        return Inertia::render('ChampionsLeague/Predictions', [
            'currentWeek' => $currentWeek,
            'predictedStandings' => $predictedStandings,
        ]);
    }

    /**
     * Maç sonucunu güncelle
     */
    public function updateMatch(UpdateMatchRequest $request, GameMatch $match): RedirectResponse
    {
        $match->update(array_merge($request->validated(), [
            'is_played' => true,
        ]));

        $this->leagueService->updateStandings();

        return redirect()->back()->with('success', 'Maç sonucu güncellendi!');
    }

    /**
     * Maçı sıfırla (oynanmamış yap)
     */
    public function resetMatch(GameMatch $match): RedirectResponse
    {
        $match->update([
            'home_score' => null,
            'away_score' => null,
            'is_played' => false,
        ]);

        $this->leagueService->updateStandings();

        return redirect()->back()->with('success', 'Maç sıfırlandı!');
    }

    public function resetAllMatches(): RedirectResponse
    {
        GameMatch::query()->update([
            'home_score' => null,
            'away_score' => null,
            'is_played' => false,
        ]);

        LeagueStanding::query()->update([
            'points' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
        ]);

        return redirect()->back()->with('success', 'Maç sıfırlandı!');
    }
}
