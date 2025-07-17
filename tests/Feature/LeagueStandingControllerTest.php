<?php

use App\Models\LeagueStanding;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->team = Team::factory()->create();
});

describe('GET /api/champions-league/standings', function () {
    test('returns all standings ordered by position', function () {
        // Arrange
        LeagueStanding::factory()->create([
            'team_id' => $this->team->id,
            'position' => 3,
        ]);
        LeagueStanding::factory()->create([
            'team_id' => $this->team->id,
            'position' => 1,
        ]);
        LeagueStanding::factory()->create([
            'team_id' => $this->team->id,
            'position' => 2,
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

        $standings = $response->json('data');
        expect($standings)->toHaveCount(3);
        expect($standings[0]['position'])->toBe(1);
        expect($standings[1]['position'])->toBe(2);
        expect($standings[2]['position'])->toBe(3);
    });

    test('returns empty array when no standings exist', function () {
        // Act
        $response = $this->getJson('/api/champions-league/standings');

        // Assert
        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(0);
    });

    test('includes team relationship data', function () {
        // Arrange
        LeagueStanding::factory()->create([
            'team_id' => $this->team->id,
        ]);

        // Act
        $response = $this->getJson('/api/champions-league/standings');

        // Assert
        $standing = $response->json('data.0');
        expect($standing['team'])->toHaveKey('id');
        expect($standing['team'])->toHaveKey('name');
        expect($standing['team']['id'])->toBe($this->team->id);
    });
});

describe('POST /api/champions-league/standings', function () {
    test('creates new standing successfully', function () {
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

    test('validates required fields', function () {
        // Act
        $response = $this->postJson('/api/champions-league/standings', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['team_id', 'position', 'points', 'goals_for', 'goals_against', 'goal_difference', 'wins', 'draws', 'losses']);
    });

    test('validates unique team', function () {
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

    test('validates position is positive integer', function () {
        // Arrange
        $standingData = [
            'team_id' => $this->team->id,
            'position' => 0, // Invalid
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
            ->assertJsonValidationErrors(['position']);
    });

    test('validates points is non-negative', function () {
        // Arrange
        $standingData = [
            'team_id' => $this->team->id,
            'position' => 1,
            'points' => -1, // Invalid
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
            ->assertJsonValidationErrors(['points']);
    });

    test('validates goals are non-negative', function () {
        // Arrange
        $standingData = [
            'team_id' => $this->team->id,
            'position' => 1,
            'points' => 0,
            'goals_for' => -1, // Invalid
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
            ->assertJsonValidationErrors(['goals_for']);
    });

    test('validates team exists', function () {
        // Arrange
        $standingData = [
            'team_id' => 999, // Non-existent team
            'position' => 1,
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
});

describe('GET /api/champions-league/standings/{id}', function () {
    test('returns specific standing with team relationship', function () {
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
            ]);

        // Check team relationship is loaded and correct
        $standingData = $response->json('data');
        expect($standingData['team']['id'])->toBe($this->team->id);
    });

    test('returns 404 for non-existent standing', function () {
        // Act
        $response = $this->getJson('/api/champions-league/standings/999');

        // Assert
        $response->assertStatus(404);
    });

    test('returns 404 for invalid standing id', function () {
        // Act
        $response = $this->getJson('/api/champions-league/standings/invalid');

        // Assert
        $response->assertStatus(404);
    });
});

describe('PUT /api/champions-league/standings/{id}', function () {
    test('updates standing successfully', function () {
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

    test('validates points is non-negative on update', function () {
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

    test('validates position is positive on update', function () {
        // Arrange
        $standing = LeagueStanding::factory()->create([
            'team_id' => $this->team->id,
        ]);
        $updateData = [
            'position' => 0, // Invalid
        ];

        // Act
        $response = $this->putJson("/api/champions-league/standings/{$standing->id}", $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['position']);
    });

    test('validates goals are non-negative on update', function () {
        // Arrange
        $standing = LeagueStanding::factory()->create([
            'team_id' => $this->team->id,
        ]);
        $updateData = [
            'goals_for' => -1, // Invalid
        ];

        // Act
        $response = $this->putJson("/api/champions-league/standings/{$standing->id}", $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['goals_for']);
    });

    test('allows partial updates', function () {
        // Arrange
        $standing = LeagueStanding::factory()->create([
            'team_id' => $this->team->id,
            'points' => 0,
            'wins' => 0,
        ]);
        $updateData = [
            'points' => 3,
        ];

        // Act
        $response = $this->putJson("/api/champions-league/standings/{$standing->id}", $updateData);

        // Assert
        $response->assertStatus(204);

        $standing->refresh();
        expect($standing->points)->toBe(3);
        expect($standing->wins)->toBe(0); // Unchanged
    });

    test('returns 404 for non-existent standing', function () {
        // Arrange
        $updateData = ['points' => 3];

        // Act
        $response = $this->putJson('/api/champions-league/standings/999', $updateData);

        // Assert
        $response->assertStatus(404);
    });
});

describe('DELETE /api/champions-league/standings/{id}', function () {
    test('deletes standing successfully', function () {
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

    test('returns 404 for non-existent standing', function () {
        // Act
        $response = $this->deleteJson('/api/champions-league/standings/999');

        // Assert
        $response->assertStatus(404);
    });

    test('returns 404 for invalid standing id', function () {
        // Act
        $response = $this->deleteJson('/api/champions-league/standings/invalid');

        // Assert
        $response->assertStatus(404);
    });
});
