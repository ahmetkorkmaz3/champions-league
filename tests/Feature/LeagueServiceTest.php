<?php

use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Models\Team;
use App\Services\LeagueService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Test takımları oluştur
    $this->team1 = Team::create([
        'name' => 'Real Madrid',
        'power_level' => 95,
        'city' => 'Madrid',
    ]);

    $this->team2 = Team::create([
        'name' => 'Manchester City',
        'power_level' => 92,
        'city' => 'Manchester',
    ]);

    $this->team3 = Team::create([
        'name' => 'Bayern Munich',
        'power_level' => 90,
        'city' => 'Munich',
    ]);

    $this->team4 = Team::create([
        'name' => 'PSG',
        'power_level' => 88,
        'city' => 'Paris',
    ]);

    $this->leagueService = new LeagueService;
});

test('update standings creates standings for all teams', function () {
    $this->leagueService->updateStandings();

    $standings = LeagueStanding::all();

    expect($standings)->toHaveCount(4)
        ->and($standings->pluck('team_id')->toArray())->toContain($this->team1->id)
        ->and($standings->pluck('team_id')->toArray())->toContain($this->team2->id)
        ->and($standings->pluck('team_id')->toArray())->toContain($this->team3->id)
        ->and($standings->pluck('team_id')->toArray())->toContain($this->team4->id);
});

test('update standings calculates correct points', function () {
    // Maç sonuçları oluştur
    GameMatch::create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'home_score' => 2,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    GameMatch::create([
        'home_team_id' => $this->team3->id,
        'away_team_id' => $this->team4->id,
        'home_score' => 1,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    $this->leagueService->updateStandings();

    $team1Standing = LeagueStanding::where('team_id', $this->team1->id)->first();
    $team2Standing = LeagueStanding::where('team_id', $this->team2->id)->first();
    $team3Standing = LeagueStanding::where('team_id', $this->team3->id)->first();
    $team4Standing = LeagueStanding::where('team_id', $this->team4->id)->first();

    // Real Madrid kazandı (3 puan)
    expect($team1Standing->points)->toBe(3)
        ->and($team1Standing->wins)->toBe(1)
        ->and($team1Standing->losses)->toBe(0)
        ->and($team1Standing->draws)->toBe(0)
        ->and($team1Standing->goals_for)->toBe(2)
        ->and($team1Standing->goals_against)->toBe(1)
        ->and($team1Standing->goal_difference)->toBe(1)
        ->and($team2Standing->points)->toBe(0)
        ->and($team2Standing->wins)->toBe(0)
        ->and($team2Standing->losses)->toBe(1)
        ->and($team2Standing->draws)->toBe(0)
        ->and($team2Standing->goals_for)->toBe(1)
        ->and($team2Standing->goals_against)->toBe(2)
        ->and($team2Standing->goal_difference)->toBe(-1)
        ->and($team3Standing->points)->toBe(1)
        ->and($team3Standing->draws)->toBe(1)
        ->and($team4Standing->points)->toBe(1)
        ->and($team4Standing->draws)->toBe(1);
});

test('update positions orders standings correctly', function () {
    // Farklı puanlarla maçlar oluştur
    GameMatch::create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'home_score' => 3,
        'away_score' => 0,
        'week' => 1,
        'is_played' => true,
    ]);

    GameMatch::create([
        'home_team_id' => $this->team3->id,
        'away_team_id' => $this->team4->id,
        'home_score' => 2,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    $this->leagueService->updateStandings();

    $standings = $this->leagueService->getCurrentStandings();

    // Real Madrid 1. sırada olmalı (3 puan)
    expect($standings[0]->team_id)->toBe($this->team1->id)
        ->and($standings[0]->position)->toBe(1)
        ->and($standings[1]->team_id)->toBe($this->team3->id)
        ->and($standings[1]->position)->toBe(2)
        ->and($standings[2]->team_id)->toBe($this->team4->id)
        ->and($standings[2]->position)->toBe(3)
        ->and($standings[3]->team_id)->toBe($this->team2->id)
        ->and($standings[3]->position)->toBe(4);
});

test('predicted standings includes simulated results', function () {
    // 1. hafta maçları (oynanmış)
    GameMatch::create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'home_score' => 2,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    GameMatch::create([
        'home_team_id' => $this->team3->id,
        'away_team_id' => $this->team4->id,
        'home_score' => 1,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    // 2. hafta maçları (oynanmamış)
    GameMatch::create([
        'home_team_id' => $this->team2->id,
        'away_team_id' => $this->team3->id,
        'week' => 2,
        'is_played' => false,
    ]);

    GameMatch::create([
        'home_team_id' => $this->team4->id,
        'away_team_id' => $this->team1->id,
        'week' => 2,
        'is_played' => false,
    ]);

    $predictedStandings = $this->leagueService->getPredictedStandings(1);

    expect($predictedStandings)->toHaveCount(4);

    // Real Madrid en yüksek puanlı olmalı (gerçek maç + simüle edilmiş maç)
    $realMadrid = collect($predictedStandings)->firstWhere('team.id', $this->team1->id);
    expect($realMadrid['points'])->toBeGreaterThanOrEqual(3);
});

test('team standing methods work correctly', function () {
    $standing = LeagueStanding::create([
        'team_id' => $this->team1->id,
        'points' => 6,
        'goals_for' => 5,
        'goals_against' => 2,
        'goal_difference' => 3,
        'wins' => 2,
        'draws' => 0,
        'losses' => 0,
        'position' => 1,
    ]);

    expect($standing->getMatchesPlayed())->toBe(2)
        ->and($standing->calculateGoalDifference())->toBe(3)
        ->and($standing->calculatePoints())->toBe(6);

    $sortingCriteria = $standing->getSortingCriteria();
    expect($sortingCriteria)->toHaveKeys(['points', 'goal_difference', 'goals_for', 'team_name'])
        ->and($sortingCriteria['points'])->toBe(6)
        ->and($sortingCriteria['goal_difference'])->toBe(3);
});
