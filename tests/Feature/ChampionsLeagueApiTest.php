<?php

use App\Models\GameMatch;
use App\Models\Team;
use App\Services\LeagueService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Test takımları oluştur
    $this->team1 = Team::create([
        'name' => 'Real Madrid',
        'power_level' => 95,
        'city' => 'Madrid',
    ]);

    $this->team2 = Team::create([
        'name' => 'Manchester City',
        'power_level' => 92,
        'city' => 'Manchester',
    ]);

    $this->team3 = Team::create([
        'name' => 'Bayern Munich',
        'power_level' => 90,
        'city' => 'Munich',
    ]);

    $this->team4 = Team::create([
        'name' => 'PSG',
        'power_level' => 88,
        'city' => 'Paris',
    ]);

    // Test maçları oluştur
    $this->match1 = GameMatch::create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'home_score' => 2,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    $this->match2 = GameMatch::create([
        'home_team_id' => $this->team3->id,
        'away_team_id' => $this->team4->id,
        'home_score' => 1,
        'away_score' => 1,
        'week' => 1,
        'is_played' => true,
    ]);

    $this->leagueService = new LeagueService;
    $this->leagueService->updateStandings();
});

test('get standings returns correct response structure', function () {
    $response = $this->getJson('/api/champions-league/standings');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'team_id',
                    'points',
                    'goals_for',
                    'goals_against',
                    'goal_difference',
                    'wins',
                    'draws',
                    'losses',
                    'position',
                    'team' => [
                        'id',
                        'name',
                        'power_level',
                        'city',
                    ],
                ],
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Lig tablosu başarıyla getirildi',
        ]);
});

test('get matches by week returns correct response', function () {
    $response = $this->getJson('/api/champions-league/matches/week/1');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'home_team_id',
                    'away_team_id',
                    'home_score',
                    'away_score',
                    'week',
                    'is_played',
                    'home_team' => [
                        'id',
                        'name',
                        'power_level',
                        'city',
                    ],
                    'away_team' => [
                        'id',
                        'name',
                        'power_level',
                        'city',
                    ],
                ],
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => '1. hafta maçları başarıyla getirildi',
        ]);

    expect($response->json('data'))->toHaveCount(2);
});

test('get all matches returns all matches', function () {
    $response = $this->getJson('/api/champions-league/matches');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'home_team_id',
                    'away_team_id',
                    'home_score',
                    'away_score',
                    'week',
                    'is_played',
                    'home_team',
                    'away_team',
                ],
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Tüm maçlar başarıyla getirildi',
        ]);

    expect($response->json('data'))->toHaveCount(2);
});

test('get teams returns all teams', function () {
    $response = $this->getJson('/api/champions-league/teams');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'power_level',
                    'city',
                    'logo',
                ],
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Takımlar başarıyla getirildi',
        ]);

    expect($response->json('data'))->toHaveCount(4);
});

test('get match returns specific match', function () {
    $response = $this->getJson("/api/champions-league/matches/{$this->match1->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'home_team_id',
                'away_team_id',
                'home_score',
                'away_score',
                'week',
                'is_played',
                'home_team',
                'away_team',
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Maç detayı başarıyla getirildi',
            'data' => [
                'id' => $this->match1->id,
                'home_team_id' => $this->team1->id,
                'away_team_id' => $this->team2->id,
                'home_score' => 2,
                'away_score' => 1,
                'week' => 1,
                'is_played' => true,
            ],
        ]);
});

test('get matches grouped by week returns correct structure', function () {
    $response = $this->getJson('/api/champions-league/matches/grouped');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '1' => [
                    '*' => [
                        'id',
                        'home_team_id',
                        'away_team_id',
                        'home_score',
                        'away_score',
                        'week',
                        'is_played',
                        'home_team',
                        'away_team',
                    ],
                ],
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Haftalara göre gruplandırılmış maçlar başarıyla getirildi',
        ]);

    expect($response->json('data.1'))->toHaveCount(2);
});

test('get matches by non-existent week returns empty array', function () {
    $response = $this->getJson('/api/champions-league/matches/week/5');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [],
            'message' => '5. hafta maçları başarıyla getirildi',
        ]);
});

test('get non-existent match returns 404', function () {
    $response = $this->getJson('/api/champions-league/matches/999');

    $response->assertStatus(404);
});
