<?php

use App\Models\LeagueStanding;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->team = Team::factory()->create();
});

test('index returns all standings', function () {
    // Arrange
    LeagueStanding::factory()->count(3)->create([
        'team_id' => $this->team->id,
    ]);

    // Act
    $response = $this->getJson('/api/champions-league/standings');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'position',
                    'points',
                    'goals_for',
                    'goals_against',
                    'goal_difference',
                    'wins',
                    'draws',
                    'losses',
                    'matches_played',
                    'team',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('store creates new standing', function () {
    // Arrange
    $standingData = [
        'team_id' => $this->team->id,
        'position' => 1,
        'points' => 3,
        'goals_for' => 2,
        'goals_against' => 1,
        'goal_difference' => 1,
        'wins' => 1,
        'draws' => 0,
        'losses' => 0,
    ];

    // Act
    $response = $this->postJson('/api/champions-league/standings', $standingData);

    // Assert
    $response->assertStatus(201);

    $this->assertDatabaseHas('league_standings', $standingData);
});

test('store validates required fields', function () {
    // Act
    $response = $this->postJson('/api/champions-league/standings', []);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_id', 'position', 'points', 'goals_for', 'goals_against', 'goal_difference', 'wins', 'draws', 'losses']);
});

test('store validates unique team', function () {
    // Arrange
    LeagueStanding::factory()->create(['team_id' => $this->team->id]);

    $standingData = [
        'team_id' => $this->team->id, // Duplicate
        'position' => 2,
        'points' => 0,
        'goals_for' => 0,
        'goals_against' => 0,
        'goal_difference' => 0,
        'wins' => 0,
        'draws' => 0,
        'losses' => 0,
    ];

    // Act
    $response = $this->postJson('/api/champions-league/standings', $standingData);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_id']);
});

test('show returns specific standing', function () {
    // Arrange
    $standing = LeagueStanding::factory()->create([
        'team_id' => $this->team->id,
    ]);

    // Act
    $response = $this->getJson("/api/champions-league/standings/{$standing->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'position',
                'points',
                'goals_for',
                'goals_against',
                'goal_difference',
                'wins',
                'draws',
                'losses',
                'matches_played',
                'team',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $standing->id,
                'team_id' => $this->team->id,
            ],
        ]);
});

test('show returns 404 for non-existent standing', function () {
    // Act
    $response = $this->getJson('/api/champions-league/standings/999');

    // Assert
    $response->assertStatus(404);
});

test('update modifies standing', function () {
    // Arrange
    $standing = LeagueStanding::factory()->create([
        'team_id' => $this->team->id,
    ]);
    $updateData = [
        'points' => 6,
        'wins' => 2,
    ];

    // Act
    $response = $this->putJson("/api/champions-league/standings/{$standing->id}", $updateData);

    // Assert
    $response->assertStatus(204);

    $standing->refresh();
    expect($standing->points)->toBe(6);
    expect($standing->wins)->toBe(2);
});

test('update validates data', function () {
    // Arrange
    $standing = LeagueStanding::factory()->create([
        'team_id' => $this->team->id,
    ]);
    $updateData = [
        'points' => -1, // Invalid
    ];

    // Act
    $response = $this->putJson("/api/champions-league/standings/{$standing->id}", $updateData);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['points']);
});

test('destroy deletes standing', function () {
    // Arrange
    $standing = LeagueStanding::factory()->create([
        'team_id' => $this->team->id,
    ]);

    // Act
    $response = $this->deleteJson("/api/champions-league/standings/{$standing->id}");

    // Assert
    $response->assertStatus(204);

    $this->assertDatabaseMissing('league_standings', ['id' => $standing->id]);
});

test('destroy returns 404 for non-existent standing', function () {
    // Act
    $response = $this->deleteJson('/api/champions-league/standings/999');

    // Assert
    $response->assertStatus(404);
});
