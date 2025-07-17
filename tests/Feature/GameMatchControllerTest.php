<?php

use App\Models\GameMatch;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Test takımları oluştur
    $this->team1 = Team::factory()->create();
    $this->team2 = Team::factory()->create();
});

test('index returns all matches', function () {
    // Arrange
    GameMatch::factory()->count(3)->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
    ]);

    // Act
    $response = $this->getJson('/api/champions-league/matches');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'week',
                    'is_played',
                    'home_score',
                    'away_score',
                    'home_team',
                    'away_team',
                    'result_string',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('index filters by week', function () {
    // Arrange
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
    ]);
    GameMatch::factory()->create([
        'home_team_id' => $this->team2->id,
        'away_team_id' => $this->team1->id,
        'week' => 2,
    ]);

    // Act
    $response = $this->getJson('/api/champions-league/matches?week=1');

    // Assert
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.week'))->toBe(1);
});

test('index filters by played status', function () {
    // Arrange
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'is_played' => true,
    ]);
    GameMatch::factory()->create([
        'home_team_id' => $this->team2->id,
        'away_team_id' => $this->team1->id,
        'is_played' => false,
    ]);

    // Act
    $response = $this->getJson('/api/champions-league/matches?played=true');

    // Assert
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.is_played'))->toBeTrue();
});

test('store creates new match', function () {
    // Arrange
    $matchData = [
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
    ];

    // Act
    $response = $this->postJson('/api/champions-league/matches', $matchData);

    // Assert
    $response->assertStatus(201);

    $this->assertDatabaseHas('game_matches', $matchData);
});

test('store validates required fields', function () {
    // Act
    $response = $this->postJson('/api/champions-league/matches', []);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['home_team_id', 'away_team_id', 'week']);
});

test('store validates different teams', function () {
    // Arrange
    $matchData = [
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team1->id, // Same team
        'week' => 1,
    ];

    // Act
    $response = $this->postJson('/api/champions-league/matches', $matchData);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['away_team_id']);
});

test('show returns specific match', function () {
    // Arrange
    $match = GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
    ]);

    // Act
    $response = $this->getJson("/api/champions-league/matches/{$match->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'week',
                'is_played',
                'home_score',
                'away_score',
                'home_team',
                'away_team',
                'result_string',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $match->id,
                'home_team_id' => $this->team1->id,
                'away_team_id' => $this->team2->id,
            ],
        ]);
});

test('show returns 404 for non-existent match', function () {
    // Act
    $response = $this->getJson('/api/champions-league/matches/999');

    // Assert
    $response->assertStatus(404);
});

test('update modifies match', function () {
    // Arrange
    $match = GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
    ]);
    $updateData = [
        'home_score' => 2,
        'away_score' => 1,
        'is_played' => true,
    ];

    // Act
    $response = $this->putJson("/api/champions-league/matches/{$match->id}", $updateData);

    // Assert
    $response->assertStatus(204);

    $match->refresh();
    expect($match->home_score)->toBe(2);
    expect($match->away_score)->toBe(1);
    expect($match->is_played)->toBeTrue();
});

test('update validates score range', function () {
    // Arrange
    $match = GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
    ]);
    $updateData = [
        'home_score' => -1, // Invalid
        'away_score' => 1,
    ];

    // Act
    $response = $this->putJson("/api/champions-league/matches/{$match->id}", $updateData);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['home_score']);
});

test('destroy deletes match', function () {
    // Arrange
    $match = GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
    ]);

    // Act
    $response = $this->deleteJson("/api/champions-league/matches/{$match->id}");

    // Assert
    $response->assertStatus(204);

    $this->assertDatabaseMissing('game_matches', ['id' => $match->id]);
});

test('byWeek returns matches grouped by week', function () {
    // Arrange
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
    ]);
    GameMatch::factory()->create([
        'home_team_id' => $this->team2->id,
        'away_team_id' => $this->team1->id,
        'week' => 1,
    ]);
    GameMatch::factory()->create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 2,
    ]);

    // Act
    $response = $this->getJson('/api/champions-league/matches/by-week');

    // Assert
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '1' => [
                    '*' => [
                        'id',
                        'week',
                        'home_team',
                        'away_team',
                    ],
                ],
                '2' => [
                    '*' => [
                        'id',
                        'week',
                        'home_team',
                        'away_team',
                    ],
                ],
            ],
        ]);

    expect($response->json('data.1'))->toHaveCount(2);
    expect($response->json('data.2'))->toHaveCount(1);
});
