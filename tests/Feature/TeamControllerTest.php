<?php

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /api/champions-league/teams', function () {
    test('returns all teams ordered by name', function () {
        // Arrange
        Team::factory()->create(['name' => 'Barcelona']);
        Team::factory()->create(['name' => 'Arsenal']);
        Team::factory()->create(['name' => 'Chelsea']);

        // Act
        $response = $this->getJson('/api/champions-league/teams');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'power_level',
                        'logo',
                        'city',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $teams = $response->json('data');
        expect($teams)->toHaveCount(3);
        expect($teams[0]['name'])->toBe('Arsenal');
        expect($teams[1]['name'])->toBe('Barcelona');
        expect($teams[2]['name'])->toBe('Chelsea');
    });

    test('returns empty array when no teams exist', function () {
        // Act
        $response = $this->getJson('/api/champions-league/teams');

        // Assert
        $response->assertStatus(200);
        expect($response->json('data'))->toHaveCount(0);
    });
});

describe('POST /api/champions-league/teams', function () {
    test('creates new team successfully', function () {
        // Arrange
        $teamData = [
            'name' => 'Barcelona',
            'power_level' => 90,
            'logo' => 'barcelona.png',
            'city' => 'Barcelona',
        ];

        // Act
        $response = $this->postJson('/api/champions-league/teams', $teamData);

        // Assert
        $response->assertStatus(201);

        $this->assertDatabaseHas('teams', $teamData);
    });

    test('validates required fields', function () {
        // Act
        $response = $this->postJson('/api/champions-league/teams', []);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'power_level', 'city']);
    });

    test('validates power level range', function () {
        // Arrange
        $teamData = [
            'name' => 'Barcelona',
            'power_level' => 150, // Invalid - too high
            'city' => 'Barcelona',
        ];

        // Act
        $response = $this->postJson('/api/champions-league/teams', $teamData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['power_level']);
    });

    test('validates power level minimum', function () {
        // Arrange
        $teamData = [
            'name' => 'Barcelona',
            'power_level' => 0, // Invalid - too low
            'city' => 'Barcelona',
        ];

        // Act
        $response = $this->postJson('/api/champions-league/teams', $teamData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['power_level']);
    });

    test('validates unique team name', function () {
        // Arrange
        Team::factory()->create(['name' => 'Barcelona']);

        $teamData = [
            'name' => 'Barcelona', // Duplicate
            'power_level' => 90,
            'city' => 'Barcelona',
        ];

        // Act
        $response = $this->postJson('/api/champions-league/teams', $teamData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('validates name length', function () {
        // Arrange
        $teamData = [
            'name' => str_repeat('A', 256), // Too long
            'power_level' => 90,
            'city' => 'Barcelona',
        ];

        // Act
        $response = $this->postJson('/api/champions-league/teams', $teamData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('validates city length', function () {
        // Arrange
        $teamData = [
            'name' => 'Barcelona',
            'power_level' => 90,
            'city' => str_repeat('A', 256), // Too long
        ];

        // Act
        $response = $this->postJson('/api/champions-league/teams', $teamData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city']);
    });
});

describe('GET /api/champions-league/teams/{id}', function () {
    test('returns specific team', function () {
        // Arrange
        $team = Team::factory()->create();

        // Act
        $response = $this->getJson("/api/champions-league/teams/{$team->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'power_level',
                    'logo',
                    'city',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $team->id,
                    'name' => $team->name,
                ],
            ]);
    });

    test('returns 404 for non-existent team', function () {
        // Act
        $response = $this->getJson('/api/champions-league/teams/999');

        // Assert
        $response->assertStatus(404);
    });
});

describe('PUT /api/champions-league/teams/{id}', function () {
    test('updates team successfully', function () {
        // Arrange
        $team = Team::factory()->create();
        $updateData = [
            'name' => 'Updated Barcelona',
            'power_level' => 95,
        ];

        // Act
        $response = $this->putJson("/api/champions-league/teams/{$team->id}", $updateData);

        // Assert
        $response->assertStatus(204);

        $team->refresh();
        expect($team->name)->toBe('Updated Barcelona');
        expect($team->power_level)->toBe(95);
    });

    test('validates power level range on update', function () {
        // Arrange
        $team = Team::factory()->create();
        $updateData = [
            'power_level' => 150, // Invalid
        ];

        // Act
        $response = $this->putJson("/api/champions-league/teams/{$team->id}", $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['power_level']);
    });

    test('validates unique team name on update', function () {
        // Arrange
        $team1 = Team::factory()->create(['name' => 'Barcelona']);
        $team2 = Team::factory()->create(['name' => 'Arsenal']);

        $updateData = [
            'name' => 'Barcelona', // Duplicate of team1
        ];

        // Act
        $response = $this->putJson("/api/champions-league/teams/{$team2->id}", $updateData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('allows updating to same name for same team', function () {
        // Arrange
        $team = Team::factory()->create(['name' => 'Barcelona']);
        $updateData = [
            'name' => 'Barcelona', // Same name
            'power_level' => 95,
        ];

        // Act
        $response = $this->putJson("/api/champions-league/teams/{$team->id}", $updateData);

        // Assert
        $response->assertStatus(204);

        $team->refresh();
        expect($team->name)->toBe('Barcelona');
        expect($team->power_level)->toBe(95);
    });

    test('returns 404 for non-existent team', function () {
        // Arrange
        $updateData = ['name' => 'Updated Team'];

        // Act
        $response = $this->putJson('/api/champions-league/teams/999', $updateData);

        // Assert
        $response->assertStatus(404);
    });
});

describe('DELETE /api/champions-league/teams/{id}', function () {
    test('deletes team successfully', function () {
        // Arrange
        $team = Team::factory()->create();

        // Act
        $response = $this->deleteJson("/api/champions-league/teams/{$team->id}");

        // Assert
        $response->assertStatus(204);

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    });

    test('returns 404 for non-existent team', function () {
        // Act
        $response = $this->deleteJson('/api/champions-league/teams/999');

        // Assert
        $response->assertStatus(404);
    });
});
