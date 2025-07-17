<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreGameMatchRequest;
use App\Http\Requests\Api\UpdateGameMatchRequest;
use App\Http\Resources\GameMatchResource;
use App\Http\Resources\LeagueStandingResource;
use App\Models\GameMatch;
use App\Services\LeagueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GameMatchController extends Controller
{
    protected LeagueService $leagueService;

    public function __construct(LeagueService $leagueService)
    {
        $this->leagueService = $leagueService;
    }

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
        $match = GameMatch::create($request->validated());
        $this->leagueService->updateStandingsForMatch($match);

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
        $this->leagueService->updateStandingsForMatch($match->fresh());

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
     * Remove the specified match
     */
    public function destroy(GameMatch $match): Response
    {
        $match->delete();
        $this->leagueService->handleMatchDeletion($match);

        return response()->noContent();
    }

    /**
     * Get matches grouped by week
     */
    public function byWeek(): JsonResponse
    {
        $matchesByWeek = $this->leagueService->getAllMatchesGroupedByWeek();

        return response()->json([
            'data' => $matchesByWeek->map(function ($weekMatches) {
                return GameMatchResource::collection($weekMatches);
            }),
        ]);
    }
}
