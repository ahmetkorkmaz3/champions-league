<?php

use App\Models\GameMatch;
use App\Models\Team;
use App\Services\MatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Create test teams with different power levels
    $this->strongTeam = Team::factory()->create([
        'name' => 'Strong Team',
        'power_level' => 90,
        'city' => 'Strong City',
    ]);

    $this->weakTeam = Team::factory()->create([
        'name' => 'Weak Team',
        'power_level' => 60,
        'city' => 'Weak City',
    ]);

    $this->averageTeam = Team::factory()->create([
        'name' => 'Average Team',
        'power_level' => 75,
        'city' => 'Average City',
    ]);

    $this->matchService = new MatchService;
});

test('simulateMatch returns valid match result with scores', function () {
    // Create a match
    $match = GameMatch::factory()->create([
        'home_team_id' => $this->strongTeam->id,
        'away_team_id' => $this->weakTeam->id,
        'week' => 1,
        'is_played' => false,
    ]);

    // Act
    $result = $this->matchService->simulateMatch($match);

    // Assert
    expect($result)->toBeArray();
    expect($result)->toHaveKeys(['home_score', 'away_score']);
    expect($result['home_score'])->toBeGreaterThanOrEqual(0);
    expect($result['away_score'])->toBeGreaterThanOrEqual(0);
    expect($result['home_score'])->toBeLessThanOrEqual(5); // Max 5 goals
    expect($result['away_score'])->toBeLessThanOrEqual(5); // Max 5 goals
});

test('simulateMatch gives home team advantage', function () {
    // Create multiple matches with same teams to test home advantage
    $results = [];

    for ($i = 0; $i < 10; $i++) {
        $match = GameMatch::factory()->create([
            'home_team_id' => $this->averageTeam->id,
            'away_team_id' => $this->averageTeam->id, // Same team to eliminate power difference
            'week' => $i + 1,
            'is_played' => false,
        ]);

        $results[] = $this->matchService->simulateMatch($match);
    }

    // Calculate average home and away scores
    $avgHomeScore = collect($results)->avg('home_score');
    $avgAwayScore = collect($results)->avg('away_score');

    // Home team should generally score more (due to 10% power boost)
    expect($avgHomeScore)->toBeGreaterThan($avgAwayScore);
});

test('simulateMatch considers team power levels', function () {
    // Create matches between strong and weak teams
    $strongHomeResults = [];
    $weakHomeResults = [];

    for ($i = 0; $i < 5; $i++) {
        // Strong team at home vs weak team
        $match1 = GameMatch::factory()->create([
            'home_team_id' => $this->strongTeam->id,
            'away_team_id' => $this->weakTeam->id,
            'week' => $i + 1,
            'is_played' => false,
        ]);

        // Weak team at home vs strong team
        $match2 = GameMatch::factory()->create([
            'home_team_id' => $this->weakTeam->id,
            'away_team_id' => $this->strongTeam->id,
            'week' => $i + 6,
            'is_played' => false,
        ]);

        $strongHomeResults[] = $this->matchService->simulateMatch($match1);
        $weakHomeResults[] = $this->matchService->simulateMatch($match2);
    }

    // Strong team should generally score more when at home
    $strongHomeAvg = collect($strongHomeResults)->avg('home_score');
    $weakHomeAvg = collect($weakHomeResults)->avg('home_score');

    expect($strongHomeAvg)->toBeGreaterThan($weakHomeAvg);
});

test('playMatch updates match with simulated result', function () {
    // Create an unplayed match
    $match = GameMatch::factory()->create([
        'home_team_id' => $this->strongTeam->id,
        'away_team_id' => $this->weakTeam->id,
        'week' => 1,
        'is_played' => false,
        'home_score' => null,
        'away_score' => null,
    ]);

    // Act
    $updatedMatch = $this->matchService->playMatch($match);

    // Assert
    expect($updatedMatch->is_played)->toBeTrue();
    expect($updatedMatch->home_score)->toBeGreaterThanOrEqual(0);
    expect($updatedMatch->away_score)->toBeGreaterThanOrEqual(0);
    expect($updatedMatch->home_score)->toBeLessThanOrEqual(5);
    expect($updatedMatch->away_score)->toBeLessThanOrEqual(5);

    // Verify the match was actually updated in database
    $freshMatch = GameMatch::find($match->id);
    expect($freshMatch->is_played)->toBeTrue();
    expect($freshMatch->home_score)->toBe($updatedMatch->home_score);
    expect($freshMatch->away_score)->toBe($updatedMatch->away_score);
});

test('playWeek plays all matches for specified week', function () {
    // Create multiple unplayed matches for week 1
    GameMatch::factory()->create([
        'home_team_id' => $this->strongTeam->id,
        'away_team_id' => $this->weakTeam->id,
        'week' => 1,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->averageTeam->id,
        'away_team_id' => $this->strongTeam->id,
        'week' => 1,
        'is_played' => false,
    ]);

    // Create a match for different week (should not be played)
    GameMatch::factory()->create([
        'home_team_id' => $this->weakTeam->id,
        'away_team_id' => $this->averageTeam->id,
        'week' => 2,
        'is_played' => false,
    ]);

    // Act
    $results = $this->matchService->playWeek(1);

    // Assert
    expect($results)->toHaveCount(2);

    foreach ($results as $match) {
        expect($match->week)->toBe(1);
        expect($match->is_played)->toBeTrue();
        expect($match->home_score)->toBeGreaterThanOrEqual(0);
        expect($match->away_score)->toBeGreaterThanOrEqual(0);
    }

    // Check that week 2 match was not played
    $week2Match = GameMatch::where('week', 2)->first();
    expect($week2Match->is_played)->toBeFalse();
});

test('playAllMatches plays all matches for all weeks', function () {
    // Create matches for different weeks (6 hafta için)
    GameMatch::factory()->create([
        'home_team_id' => $this->strongTeam->id,
        'away_team_id' => $this->weakTeam->id,
        'week' => 1,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->averageTeam->id,
        'away_team_id' => $this->strongTeam->id,
        'week' => 2,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->weakTeam->id,
        'away_team_id' => $this->averageTeam->id,
        'week' => 3,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->weakTeam->id,
        'away_team_id' => $this->strongTeam->id,
        'week' => 4,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->strongTeam->id,
        'away_team_id' => $this->averageTeam->id,
        'week' => 5,
        'is_played' => false,
    ]);

    GameMatch::factory()->create([
        'home_team_id' => $this->averageTeam->id,
        'away_team_id' => $this->weakTeam->id,
        'week' => 6,
        'is_played' => false,
    ]);

    // Act
    $allResults = $this->matchService->playAllMatches();

    // Assert
    expect($allResults)->toBeArray();
    expect($allResults)->toHaveKeys([1, 2, 3, 4, 5, 6]); // All 6 weeks

    // Check that all matches were played
    $allMatches = GameMatch::all();
    expect($allMatches)->toHaveCount(6);

    foreach ($allMatches as $match) {
        expect($match->is_played)->toBeTrue();
        expect($match->home_score)->toBeGreaterThanOrEqual(0);
        expect($match->away_score)->toBeGreaterThanOrEqual(0);
    }

    // Check that results are organized by week
    foreach ($allResults as $week => $weekResults) {
        expect($weekResults)->toHaveCount(1); // 1 match per week
    }
});

test('simulateMatch produces realistic score distributions', function () {
    $results = [];

    // Simulate many matches to test distribution
    for ($i = 0; $i < 50; $i++) {
        $match = GameMatch::factory()->create([
            'home_team_id' => $this->averageTeam->id,
            'away_team_id' => $this->averageTeam->id,
            'week' => $i + 1,
            'is_played' => false,
        ]);

        $results[] = $this->matchService->simulateMatch($match);
    }

    $homeScores = collect($results)->pluck('home_score');
    $awayScores = collect($results)->pluck('away_score');

    // Test that scores are within reasonable bounds
    expect($homeScores->max())->toBeLessThanOrEqual(5);
    expect($awayScores->max())->toBeLessThanOrEqual(5);
    expect($homeScores->min())->toBeGreaterThanOrEqual(0);
    expect($awayScores->min())->toBeGreaterThanOrEqual(0);

    // Test that we get some variety in scores (not all 0-0)
    $totalGoals = $homeScores->sum() + $awayScores->sum();
    expect($totalGoals)->toBeGreaterThan(0);

    // Test that most matches have reasonable total goals
    $totalGoalsPerMatch = collect($results)->map(function ($result) {
        return $result['home_score'] + $result['away_score'];
    });

    expect($totalGoalsPerMatch->avg())->toBeGreaterThan(0.5); // At least some goals
    expect($totalGoalsPerMatch->avg())->toBeLessThan(4); // Not too many goals
});
