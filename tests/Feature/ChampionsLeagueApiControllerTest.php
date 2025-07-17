<?php

use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Test takımları oluştur
    $this->team1 = Team::factory()->create();
    $this->team2 = Team::factory()->create();
    $this->team3 = Team::factory()->create();
    $this->team4 = Team::factory()->create();
});

test('play week simulates matches for specific week', function () {
    // Arrange
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
        'is_played' => false,
    ]);
    GameMatch::factory()->create([
        'home_team_id' => $this->team3->id,
        'away_team_id' => $this->team4->id,
        'week' => 1,
        'is_played' => false,
    ]);

    // Act
    $response = $this->postJson('/api/champions-league/matches/play-week', ['week' => 1]);

    // Assert
    $response->assertStatus(204);

    $matches = GameMatch::where('week', 1)->get();
    expect($matches)->toHaveCount(2);
    expect($matches->every(fn ($match) => $match->is_played))->toBeTrue();
    expect($matches->every(fn ($match) => $match->home_score !== null))->toBeTrue();
    expect($matches->every(fn ($match) => $match->away_score !== null))->toBeTrue();
});

test('play week validates week parameter', function () {
    // Act
    $response = $this->postJson('/api/champions-league/matches/play-week', []);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['week']);
});

test('play week validates week is positive', function () {
    // Act
    $response = $this->postJson('/api/champions-league/matches/play-week', ['week' => 0]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['week']);
});

test('play all matches simulates all unplayed matches', function () {
    // Arrange
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
        'is_played' => false,
    ]);
    GameMatch::factory()->create([
        'home_team_id' => $this->team3->id,
        'away_team_id' => $this->team4->id,
        'week' => 2,
        'is_played' => false,
    ]);
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team3->id,
        'week' => 3,
        'is_played' => false,
    ]);

    // Act
    $response = $this->postJson('/api/champions-league/matches/play-all');

    // Assert
    $response->assertStatus(204);

    $allMatches = GameMatch::all();
    expect($allMatches)->toHaveCount(3);
    expect($allMatches->every(fn ($match) => $match->is_played))->toBeTrue();
});

test('reset matches clears all match results and standings', function () {
    // Arrange
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
        'is_played' => true,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    LeagueStanding::factory()->create([
        'team_id' => $this->team1->id,
        'points' => 3,
        'wins' => 1,
    ]);

    // Act
    $response = $this->postJson('/api/champions-league/matches/reset');

    // Assert
    $response->assertStatus(204);

    // Check matches are reset
    $match = GameMatch::first();
    expect($match->is_played)->toBeFalse();
    expect($match->home_score)->toBeNull();
    expect($match->away_score)->toBeNull();

    // Check standings are reset
    $standing = LeagueStanding::first();
    expect($standing->points)->toBe(0);
    expect($standing->wins)->toBe(0);
    expect($standing->draws)->toBe(0);
    expect($standing->losses)->toBe(0);
    expect($standing->goals_for)->toBe(0);
    expect($standing->goals_against)->toBe(0);
    expect($standing->goal_difference)->toBe(0);
});

test('play week updates standings correctly', function () {
    // Arrange
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
        'is_played' => false,
    ]);

    // Act
    $response = $this->postJson('/api/champions-league/matches/play-week', ['week' => 1]);

    // Assert
    $response->assertStatus(204);

    // Check standings are created and updated
    $standings = LeagueStanding::all();
    expect($standings)->toHaveCount(2);

    $team1Standing = $standings->where('team_id', $this->team1->id)->first();
    $team2Standing = $standings->where('team_id', $this->team2->id)->first();

    expect($team1Standing)->not->toBeNull();
    expect($team2Standing)->not->toBeNull();

    // One team should have points (winner)
    $totalPoints = $team1Standing->points + $team2Standing->points;
    expect($totalPoints)->toBeGreaterThan(0);
});

test('play all matches creates standings for all teams', function () {
    // Arrange
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
        'is_played' => false,
    ]);
    GameMatch::factory()->create([
        'home_team_id' => $this->team3->id,
        'away_team_id' => $this->team4->id,
        'week' => 1,
        'is_played' => false,
    ]);

    // Act
    $response = $this->postJson('/api/champions-league/matches/play-all');

    // Assert
    $response->assertStatus(204);

    // Check standings are created for all teams
    $standings = LeagueStanding::all();
    expect($standings)->toHaveCount(4);

    $teamIds = $standings->pluck('team_id')->toArray();
    expect($teamIds)->toContain($this->team1->id);
    expect($teamIds)->toContain($this->team2->id);
    expect($teamIds)->toContain($this->team3->id);
    expect($teamIds)->toContain($this->team4->id);
});
