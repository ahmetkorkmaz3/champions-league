<?php

use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Models\Team;
use App\Services\LeagueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Create test teams
    $this->teamA = Team::factory()->create([
        'name' => 'Team A',
        'power_level' => 85,
        'city' => 'City A',
    ]);

    $this->teamB = Team::factory()->create([
        'name' => 'Team B',
        'power_level' => 80,
        'city' => 'City B',
    ]);

    $this->teamC = Team::factory()->create([
        'name' => 'Team C',
        'power_level' => 75,
        'city' => 'City C',
    ]);

    $this->leagueService = new LeagueService;
});

test('updateStandingsForMatch updates only affected teams', function () {
    // Create a third team that won't be affected
    $teamD = Team::factory()->create([
        'name' => 'Team D',
        'power_level' => 70,
        'city' => 'City D',
    ]);

    // Create initial standings for all teams
    LeagueStanding::create([
        'team_id' => $this->teamA->id,
        'points' => 3,
        'goals_for' => 2,
        'goals_against' => 1,
        'goal_difference' => 1,
        'wins' => 1,
        'draws' => 0,
        'losses' => 0,
    ]);

    LeagueStanding::create([
        'team_id' => $this->teamB->id,
        'points' => 0,
        'goals_for' => 1,
        'goals_against' => 2,
        'goal_difference' => -1,
        'wins' => 0,
        'draws' => 0,
        'losses' => 1,
    ]);

    LeagueStanding::create([
        'team_id' => $teamD->id,
        'points' => 6,
        'goals_for' => 4,
        'goals_against' => 0,
        'goal_difference' => 4,
        'wins' => 2,
        'draws' => 0,
        'losses' => 0,
    ]);

    // Create a match between team A and team B
    $match = GameMatch::factory()->create([
        'home_team_id' => $this->teamA->id,
        'away_team_id' => $this->teamB->id,
        'home_score' => 3,
        'away_score' => 0,
        'week' => 1,
        'is_played' => true,
    ]);

    // Act
    $this->leagueService->updateStandingsForMatch($match);

    // Assert
    $teamAStanding = LeagueStanding::where('team_id', $this->teamA->id)->first();
    $teamBStanding = LeagueStanding::where('team_id', $this->teamB->id)->first();
    $teamDStanding = LeagueStanding::where('team_id', $teamD->id)->first();

    // Team A should have updated stats (only from the new match since standings are reset)
    expect($teamAStanding->points)->toBe(3); // Only the new win
    expect($teamAStanding->wins)->toBe(1); // Only the new win
    expect($teamAStanding->goals_for)->toBe(3); // Only the new goals

    // Team B should have updated stats (only from the new match since standings are reset)
    expect($teamBStanding->points)->toBe(0); // Still 0
    expect($teamBStanding->losses)->toBe(1); // Only the new loss
    expect($teamBStanding->goals_against)->toBe(3); // Only the new goals against

    // Team D should remain unchanged
    expect($teamDStanding->points)->toBe(6);
    expect($teamDStanding->wins)->toBe(2);
    expect($teamDStanding->goals_for)->toBe(4);
});

test('updateTeamStanding calculates correct statistics for a team', function () {
    // Create played matches
    GameMatch::factory()->create([
        'home_team_id' => $this->teamA->id,
        'away_team_id' => $this->teamB->id,
        'home_score' => 2,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->teamC->id,
        'away_team_id' => $this->teamA->id,
        'home_score' => 0,
        'away_score' => 2,
        'week' => 2,
        'is_played' => true,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->teamA->id,
        'away_team_id' => $this->teamC->id,
        'home_score' => 1,
        'away_score' => 1,
        'week' => 3,
        'is_played' => true,
    ]);

    // Act
    $this->leagueService->updateTeamStanding($this->teamA);

    // Assert
    $standing = LeagueStanding::where('team_id', $this->teamA->id)->first();

    expect($standing)->not->toBeNull();
    expect($standing->points)->toBe(7); // 2 wins (6) + 1 draw (1)
    expect($standing->wins)->toBe(2);
    expect($standing->draws)->toBe(1);
    expect($standing->losses)->toBe(0);
    expect($standing->goals_for)->toBe(5); // 2+2+1
    expect($standing->goals_against)->toBe(2); // 1+0+1
    expect($standing->goal_difference)->toBe(3);
});

test('updateStandings updates all teams standings', function () {
    // Create matches for all teams
    GameMatch::factory()->create([
        'home_team_id' => $this->teamA->id,
        'away_team_id' => $this->teamB->id,
        'home_score' => 2,
        'away_score' => 0,
        'week' => 1,
        'is_played' => true,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->teamB->id,
        'away_team_id' => $this->teamC->id,
        'home_score' => 1,
        'away_score' => 1,
        'week' => 2,
        'is_played' => true,
    ]);

    // Act
    $this->leagueService->updateStandings();

    // Assert
    $standings = LeagueStanding::all();
    expect($standings)->toHaveCount(3);

    $teamAStanding = $standings->where('team_id', $this->teamA->id)->first();
    expect($teamAStanding->points)->toBe(3); // 1 win

    $teamBStanding = $standings->where('team_id', $this->teamB->id)->first();
    expect($teamBStanding->points)->toBe(1); // 1 draw

    $teamCStanding = $standings->where('team_id', $this->teamC->id)->first();
    expect($teamCStanding->points)->toBe(1); // 1 draw
});

test('updatePositions assigns correct positions based on points and goal difference', function () {
    // Create standings with different points
    LeagueStanding::create([
        'team_id' => $this->teamA->id,
        'points' => 6,
        'goals_for' => 5,
        'goals_against' => 2,
        'goal_difference' => 3,
        'wins' => 2,
        'draws' => 0,
        'losses' => 0,
    ]);

    LeagueStanding::create([
        'team_id' => $this->teamB->id,
        'points' => 6,
        'goals_for' => 4,
        'goals_against' => 2,
        'goal_difference' => 2,
        'wins' => 2,
        'draws' => 0,
        'losses' => 0,
    ]);

    LeagueStanding::create([
        'team_id' => $this->teamC->id,
        'points' => 3,
        'goals_for' => 3,
        'goals_against' => 3,
        'goal_difference' => 0,
        'wins' => 1,
        'draws' => 0,
        'losses' => 1,
    ]);

    // Act
    $this->leagueService->updatePositions();

    // Assert
    $standings = LeagueStanding::orderBy('position')->get();

    expect($standings[0]->team_id)->toBe($this->teamA->id); // 6 points, +3 GD
    expect($standings[0]->position)->toBe(1);

    expect($standings[1]->team_id)->toBe($this->teamB->id); // 6 points, +2 GD
    expect($standings[1]->position)->toBe(2);

    expect($standings[2]->team_id)->toBe($this->teamC->id); // 3 points
    expect($standings[2]->position)->toBe(3);
});

test('getCurrentStandings returns standings ordered by position', function () {
    // Create standings with positions
    LeagueStanding::create([
        'team_id' => $this->teamA->id,
        'points' => 6,
        'position' => 1,
        'goals_for' => 5,
        'goals_against' => 2,
        'goal_difference' => 3,
        'wins' => 2,
        'draws' => 0,
        'losses' => 0,
    ]);

    LeagueStanding::create([
        'team_id' => $this->teamB->id,
        'points' => 3,
        'position' => 2,
        'goals_for' => 3,
        'goals_against' => 3,
        'goal_difference' => 0,
        'wins' => 1,
        'draws' => 0,
        'losses' => 1,
    ]);

    // Act
    $standings = $this->leagueService->getCurrentStandings();

    // Assert
    expect($standings)->toHaveCount(2);
    expect($standings[0]->position)->toBe(1);
    expect($standings[0]->team->id)->toBe($this->teamA->id);
    expect($standings[1]->position)->toBe(2);
    expect($standings[1]->team->id)->toBe($this->teamB->id);
});

test('getPredictedStandings returns sorted predictions after specified week', function () {
    // Create some played matches
    GameMatch::factory()->create([
        'home_team_id' => $this->teamA->id,
        'away_team_id' => $this->teamB->id,
        'home_score' => 2,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    // Create unplayed matches for future weeks
    GameMatch::factory()->create([
        'home_team_id' => $this->teamB->id,
        'away_team_id' => $this->teamC->id,
        'week' => 2,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->teamC->id,
        'away_team_id' => $this->teamA->id,
        'week' => 3,
        'is_played' => false,
    ]);

    // Act
    $predictedStandings = $this->leagueService->getPredictedStandings(1);

    // Assert
    expect($predictedStandings)->toBeArray();
    expect($predictedStandings)->toHaveCount(3);

    // Check that standings are sorted by points (descending)
    expect($predictedStandings[0]['points'])->toBeGreaterThanOrEqual($predictedStandings[1]['points']);
    expect($predictedStandings[1]['points'])->toBeGreaterThanOrEqual($predictedStandings[2]['points']);

    // Check that positions are assigned correctly
    expect($predictedStandings[0]['position'])->toBe(1);
    expect($predictedStandings[1]['position'])->toBe(2);
    expect($predictedStandings[2]['position'])->toBe(3);

    // Check that all teams have required fields
    foreach ($predictedStandings as $standing) {
        expect($standing)->toHaveKeys([
            'position', 'team', 'points', 'goals_for', 'goals_against',
            'goal_difference', 'wins', 'draws', 'losses', 'matches_played',
        ]);
    }
});

test('playWeek plays all matches for specified week', function () {
    // Create unplayed matches for week 1
    GameMatch::factory()->create([
        'home_team_id' => $this->teamA->id,
        'away_team_id' => $this->teamB->id,
        'week' => 1,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->teamC->id,
        'away_team_id' => $this->teamA->id,
        'week' => 1,
        'is_played' => false,
    ]);

    // Act
    $this->leagueService->playWeek(1);

    // Assert
    $playedMatches = GameMatch::where('week', 1)->where('is_played', true)->get();
    expect($playedMatches)->toHaveCount(2);

    foreach ($playedMatches as $match) {
        expect($match->home_score)->toBeGreaterThanOrEqual(0);
        expect($match->away_score)->toBeGreaterThanOrEqual(0);
        expect($match->is_played)->toBeTrue();
    }
});

test('playAllMatches plays all unplayed matches', function () {
    // Create unplayed matches for different weeks
    GameMatch::factory()->create([
        'home_team_id' => $this->teamA->id,
        'away_team_id' => $this->teamB->id,
        'week' => 1,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->teamB->id,
        'away_team_id' => $this->teamC->id,
        'week' => 2,
        'is_played' => false,
    ]);

    // Act
    $this->leagueService->playAllMatches();

    // Assert
    $allMatches = GameMatch::all();
    expect($allMatches)->toHaveCount(2);

    foreach ($allMatches as $match) {
        expect($match->is_played)->toBeTrue();
        expect($match->home_score)->toBeGreaterThanOrEqual(0);
        expect($match->away_score)->toBeGreaterThanOrEqual(0);
    }
});
