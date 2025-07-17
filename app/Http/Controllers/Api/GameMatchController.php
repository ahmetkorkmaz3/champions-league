<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreGameMatchRequest;
use App\Http\Requests\Api\UpdateGameMatchRequest;
use App\Http\Resources\GameMatchResource;
use App\Models\GameMatch;
use App\Services\LeagueService;
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
    public function store(StoreGameMatchRequest $request, LeagueService $leagueService): Response
    {
        $match = GameMatch::create($request->validated());

        $leagueService->updateStandingsForMatch($match);

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
    public function update(UpdateGameMatchRequest $request, GameMatch $match, LeagueService $leagueService): JsonResponse
    {
        $match->update($request->validated());

        $leagueService->updateStandingsForMatch($match->fresh());

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
    public function destroy(GameMatch $match, LeagueService $leagueService): Response
    {
        // Maç silinmeden önce etkilenen takımları kaydet
        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;

        $match->delete();

        // Maç silindikten sonra etkilenen takımların lig durumunu yeniden hesapla
        $leagueService->updateTeamStanding($homeTeam);
        $leagueService->updateTeamStanding($awayTeam);
        $leagueService->updatePositions();

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
