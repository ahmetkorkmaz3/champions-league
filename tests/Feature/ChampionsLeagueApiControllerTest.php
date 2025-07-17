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

describe('POST /api/champions-league/matches/play-week', function () {
    test('simulates matches for specific week successfully', function () {
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

    test('validates week parameter is required', function () {
        // Act
        $response = $this->postJson('/api/champions-league/matches/play-week', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['week']);
    });

    test('validates week parameter is positive integer', function () {
        // Act
        $response = $this->postJson('/api/champions-league/matches/play-week', ['week' => 0]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['week']);
    });

    test('validates week parameter is integer', function () {
        // Act
        $response = $this->postJson('/api/champions-league/matches/play-week', ['week' => 'invalid']);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['week']);
    });

    test('updates standings correctly after playing week', function () {
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

        // Check standings are created and updated for all teams
        $standings = LeagueStanding::all();
        expect($standings)->toHaveCount(4); // All 4 teams get standings

        $team1Standing = $standings->where('team_id', $this->team1->id)->first();
        $team2Standing = $standings->where('team_id', $this->team2->id)->first();

        expect($team1Standing)->not->toBeNull();
        expect($team2Standing)->not->toBeNull();

        // One team should have points (winner)
        $totalPoints = $team1Standing->points + $team2Standing->points;
        expect($totalPoints)->toBeGreaterThan(0);
    });

    test('handles week with no matches gracefully', function () {
        // Act
        $response = $this->postJson('/api/champions-league/matches/play-week', ['week' => 5]);

        // Assert
        $response->assertStatus(204);
    });
});

describe('POST /api/champions-league/matches/play-all', function () {
    test('simulates all unplayed matches successfully', function () {
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

    test('creates standings for all teams after playing all matches', function () {
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

    test('handles no matches gracefully', function () {
        // Act
        $response = $this->postJson('/api/champions-league/matches/play-all');

        // Assert
        $response->assertStatus(204);
    });

    test('skips already played matches', function () {
        // Arrange
        GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'week' => 1,
            'is_played' => true,
            'home_score' => 2,
            'away_score' => 1,
        ]);
        GameMatch::factory()->create([
            'home_team_id' => $this->team3->id,
            'away_team_id' => $this->team4->id,
            'week' => 2,
            'is_played' => false,
        ]);

        // Act
        $response = $this->postJson('/api/champions-league/matches/play-all');

        // Assert
        $response->assertStatus(204);

        // Check only unplayed match was processed
        $playedMatch = GameMatch::where('week', 1)->first();
        $unplayedMatch = GameMatch::where('week', 2)->first();

        expect($playedMatch->home_score)->toBe(2);
        expect($playedMatch->away_score)->toBe(1);
        expect($unplayedMatch->is_played)->toBeTrue();
        expect($unplayedMatch->home_score)->not->toBeNull();
        expect($unplayedMatch->away_score)->not->toBeNull();
    });
});

describe('POST /api/champions-league/matches/reset', function () {
    test('resets all match results and standings successfully', function () {
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

        // Check standings are reset (if any)
        $standing = LeagueStanding::first();
        if ($standing) {
            expect($standing->points)->toBe(0);
            expect($standing->wins)->toBe(0);
            expect($standing->draws)->toBe(0);
            expect($standing->losses)->toBe(0);
            expect($standing->goals_for)->toBe(0);
            expect($standing->goals_against)->toBe(0);
            expect($standing->goal_difference)->toBe(0);
        }
    });

    test('handles empty database gracefully', function () {
        // Act
        $response = $this->postJson('/api/champions-league/matches/reset');

        // Assert
        $response->assertStatus(204);
    });

    test('resets multiple matches and standings', function () {
        // Arrange
        GameMatch::factory()->count(3)->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'is_played' => true,
            'home_score' => 2,
            'away_score' => 1,
        ]);

        LeagueStanding::factory()->count(2)->create([
            'points' => 3,
            'wins' => 1,
        ]);

        // Act
        $response = $this->postJson('/api/champions-league/matches/reset');

        // Assert
        $response->assertStatus(204);

        // Check all matches are reset
        $matches = GameMatch::all();
        expect($matches->every(fn ($match) => ! $match->is_played))->toBeTrue();
        expect($matches->every(fn ($match) => $match->home_score === null))->toBeTrue();
        expect($matches->every(fn ($match) => $match->away_score === null))->toBeTrue();

        // Check all standings are reset
        $standings = LeagueStanding::all();
        expect($standings->every(fn ($standing) => $standing->points === 0))->toBeTrue();
    });
});
