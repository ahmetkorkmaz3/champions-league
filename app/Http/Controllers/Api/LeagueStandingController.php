<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLeagueStandingRequest;
use App\Http\Requests\Api\UpdateLeagueStandingRequest;
use App\Http\Resources\LeagueStandingResource;
use App\Models\LeagueStanding;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LeagueStandingController extends Controller
{
    /**
     * Display a listing of standings
     */
    public function index(): AnonymousResourceCollection
    {
        $standings = LeagueStanding::with('team')
            ->orderBy('position', 'asc')
            ->get();

        return LeagueStandingResource::collection($standings);
    }

    /**
     * Store a newly created standing
     */
    public function store(StoreLeagueStandingRequest $request): Response
    {
        LeagueStanding::create($request->validated());

        return response()->noContent(201);
    }

    /**
     * Display the specified standing
     */
    public function show(LeagueStanding $standing): LeagueStandingResource
    {
        $standing->load('team');

        return LeagueStandingResource::make($standing);
    }

    /**
     * Update the specified standing
     */
    public function update(UpdateLeagueStandingRequest $request, LeagueStanding $standing): Response
    {
        $standing->update($request->validated());

        return response()->noContent();
    }

    /**
     * Remove the specified standing
     */
    public function destroy(LeagueStanding $standing): Response
    {
        $standing->delete();

        return response()->noContent();
    }
} 