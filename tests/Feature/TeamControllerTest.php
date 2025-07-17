<?php

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('index returns all teams', function () {
    // Arrange
    Team::factory()->count(3)->create();

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

    expect($response->json('data'))->toHaveCount(3);
});

test('store creates new team', function () {
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

test('store validates required fields', function () {
    // Act
    $response = $this->postJson('/api/champions-league/teams', []);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'power_level', 'city']);
});

test('store validates power level range', function () {
    // Arrange
    $teamData = [
        'name' => 'Barcelona',
        'power_level' => 150, // Invalid
        'city' => 'Barcelona',
    ];

    // Act
    $response = $this->postJson('/api/champions-league/teams', $teamData);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['power_level']);
});

test('store validates unique team name', function () {
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

test('show returns specific team', function () {
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

test('show returns 404 for non-existent team', function () {
    // Act
    $response = $this->getJson('/api/champions-league/teams/999');

    // Assert
    $response->assertStatus(404);
});

test('update modifies team', function () {
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

test('update validates data', function () {
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

test('destroy deletes team', function () {
    // Arrange
    $team = Team::factory()->create();

    // Act
    $response = $this->deleteJson("/api/champions-league/teams/{$team->id}");

    // Assert
    $response->assertStatus(204);

    $this->assertDatabaseMissing('teams', ['id' => $team->id]);
});

test('destroy returns 404 for non-existent team', function () {
    // Act
    $response = $this->deleteJson('/api/champions-league/teams/999');

    // Assert
    $response->assertStatus(404);
});
