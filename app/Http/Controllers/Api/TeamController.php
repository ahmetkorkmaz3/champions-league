<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTeamRequest;
use App\Http\Requests\Api\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    /**
     * Display a listing of teams
     */
    public function index(): AnonymousResourceCollection
    {
        $teams = Team::orderBy('name')->get();

        return TeamResource::collection($teams);
    }

    /**
     * Store a newly created team
     */
    public function store(StoreTeamRequest $request): Response
    {
        Team::create($request->validated());

        return response()->noContent(201);
    }

    /**
     * Display the specified team
     */
    public function show(Team $team): TeamResource
    {
        return TeamResource::make($team);
    }

    /**
     * Update the specified team
     */
    public function update(UpdateTeamRequest $request, Team $team): Response
    {
        $team->update($request->validated());

        return response()->noContent();
    }

    /**
     * Remove the specified team
     */
    public function destroy(Team $team): Response
    {
        $team->delete();

        return response()->noContent();
    }
}
