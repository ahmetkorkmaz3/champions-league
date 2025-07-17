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

describe('GET /api/champions-league/matches', function () {
    test('returns all matches ordered by week and id', function () {
        // Arrange
        GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'week' => 2,
        ]);
        GameMatch::factory()->create([
            'home_team_id' => $this->team2->id,
            'away_team_id' => $this->team1->id,
            'week' => 1,
        ]);
        GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'week' => 1,
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

        $matches = $response->json('data');
        expect($matches)->toHaveCount(3);
        expect($matches[0]['week'])->toBe(1);
        expect($matches[1]['week'])->toBe(1);
        expect($matches[2]['week'])->toBe(2);
    });

    test('filters by week parameter', function () {
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

    test('filters by played status', function () {
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

    test('filters by unplayed status', function () {
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
        $response = $this->getJson('/api/champions-league/matches?played=false');

        // Assert
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.is_played'))->toBeFalse();
    });

    test('combines multiple filters', function () {
        // Arrange
        GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'week' => 1,
            'is_played' => true,
        ]);
        GameMatch::factory()->create([
            'home_team_id' => $this->team2->id,
            'away_team_id' => $this->team1->id,
            'week' => 1,
            'is_played' => false,
        ]);
        GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'week' => 2,
            'is_played' => true,
        ]);

        // Act
        $response = $this->getJson('/api/champions-league/matches?week=1&played=true');

        // Assert
        expect($response->json('data'))->toHaveCount(1);
        expect($response->json('data.0.week'))->toBe(1);
        expect($response->json('data.0.is_played'))->toBeTrue();
    });

    test('returns empty array when no matches exist', function () {
        // Act
        $response = $this->getJson('/api/champions-league/matches');

        // Assert
        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(0);
    });
});

describe('POST /api/champions-league/matches', function () {
    test('creates new match successfully', function () {
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

        $this->assertDatabaseHas('matches', $matchData);
    });

    test('validates required fields', function () {
        // Act
        $response = $this->postJson('/api/champions-league/matches', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['home_team_id', 'away_team_id', 'week']);
    });

    test('validates different teams', function () {
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

    test('validates week is positive integer', function () {
        // Arrange
        $matchData = [
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'week' => 0, // Invalid
        ];

        // Act
        $response = $this->postJson('/api/champions-league/matches', $matchData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['week']);
    });

    test('validates teams exist', function () {
        // Arrange
        $matchData = [
            'home_team_id' => 999, // Non-existent team
            'away_team_id' => $this->team2->id,
            'week' => 1,
        ];

        // Act
        $response = $this->postJson('/api/champions-league/matches', $matchData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['home_team_id']);
    });
});

describe('GET /api/champions-league/matches/{id}', function () {
    test('returns specific match with team relationships', function () {
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
                ],
            ]);

        // Check team relationships are loaded
        $matchData = $response->json('data');
        expect($matchData['home_team']['id'])->toBe($this->team1->id);
        expect($matchData['away_team']['id'])->toBe($this->team2->id);
    });

    test('returns 404 for non-existent match', function () {
        // Act
        $response = $this->getJson('/api/champions-league/matches/999');

        // Assert
        $response->assertStatus(404);
    });

    test('returns 404 for invalid match id', function () {
        // Act
        $response = $this->getJson('/api/champions-league/matches/invalid');

        // Assert
        $response->assertStatus(404);
    });
});

describe('PUT /api/champions-league/matches/{id}', function () {
    test('updates match successfully', function () {
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

    test('validates score range', function () {
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

    test('validates score is integer', function () {
        // Arrange
        $match = GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
        ]);
        $updateData = [
            'home_score' => 'invalid',
            'away_score' => 1,
        ];

        // Act
        $response = $this->putJson("/api/champions-league/matches/{$match->id}", $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['home_score']);
    });

    test('allows partial updates', function () {
        // Arrange
        $match = GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'home_score' => null,
            'away_score' => null,
            'is_played' => false,
        ]);
        $updateData = [
            'home_score' => 2,
        ];

        // Act
        $response = $this->putJson("/api/champions-league/matches/{$match->id}", $updateData);

        // Assert
        $response->assertStatus(204);

        $match->refresh();
        expect($match->home_score)->toBe(2);
        expect($match->away_score)->toBeNull();
        expect($match->is_played)->toBeFalse();
    });

    test('returns 404 for non-existent match', function () {
        // Arrange
        $updateData = ['home_score' => 2];

        // Act
        $response = $this->putJson('/api/champions-league/matches/999', $updateData);

        // Assert
        $response->assertStatus(404);
    });
});

describe('DELETE /api/champions-league/matches/{id}', function () {
    test('deletes match successfully', function () {
        // Arrange
        $match = GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
        ]);

        // Act
        $response = $this->deleteJson("/api/champions-league/matches/{$match->id}");

        // Assert
        $response->assertStatus(204);

        $this->assertDatabaseMissing('matches', ['id' => $match->id]);
    });

    test('returns 404 for non-existent match', function () {
        // Act
        $response = $this->deleteJson('/api/champions-league/matches/999');

        // Assert
        $response->assertStatus(404);
    });

    test('returns 404 for invalid match id', function () {
        // Act
        $response = $this->deleteJson('/api/champions-league/matches/invalid');

        // Assert
        $response->assertStatus(404);
    });
});

describe('GET /api/champions-league/matches/by-week', function () {
    test('returns matches grouped by week', function () {
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

    test('returns empty object when no matches exist', function () {
        // Act
        $response = $this->getJson('/api/champions-league/matches/by-week');

        // Assert
        $response->assertStatus(200);
        expect($response->json('data'))->toBe([]);
    });

    test('orders matches within each week by id', function () {
        // Arrange
        $match1 = GameMatch::factory()->create([
            'home_team_id' => $this->team1->id,
            'away_team_id' => $this->team2->id,
            'week' => 1,
        ]);
        $match2 = GameMatch::factory()->create([
            'home_team_id' => $this->team2->id,
            'away_team_id' => $this->team1->id,
            'week' => 1,
        ]);

        // Act
        $response = $this->getJson('/api/champions-league/matches/by-week');

        // Assert
        $week1Matches = $response->json('data.1');
        expect($week1Matches[0]['id'])->toBe($match1->id);
        expect($week1Matches[1]['id'])->toBe($match2->id);
    });
});
