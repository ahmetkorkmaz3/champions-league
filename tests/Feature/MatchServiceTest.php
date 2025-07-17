<?php

use App\Models\GameMatch;
use App\Models\Team;
use App\Services\MatchService;
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

    $this->match = GameMatch::create([
        'home_team_id' => $this->team1->id,
        'away_team_id' => $this->team2->id,
        'week' => 1,
        'is_played' => false,
    ]);

    $this->matchService = new MatchService;
});

test('simulate match returns valid result', function () {
    $result = $this->matchService->simulateMatch($this->match);

    expect($result)->toHaveKeys(['home_score', 'away_score'])
        ->and($result['home_score'])->toBeInt()
        ->and($result['away_score'])->toBeInt()
        ->and($result['home_score'])->toBeGreaterThanOrEqual(0)
        ->and($result['away_score'])->toBeGreaterThanOrEqual(0)
        ->and($result['home_score'])->toBeLessThanOrEqual(5)
        ->and($result['away_score'])->toBeLessThanOrEqual(5);
});

test('play match updates match result', function () {
    $this->matchService->playMatch($this->match);

    $this->match->refresh();

    expect($this->match->is_played)->toBeTrue()
        ->and($this->match->home_score)->toBeInt()
        ->and($this->match->away_score)->toBeInt()
        ->and($this->match->home_score)->toBeGreaterThanOrEqual(0)
        ->and($this->match->away_score)->toBeGreaterThanOrEqual(0);
});

test('play week processes all matches in week', function () {
    // İkinci bir maç ekle
    $team3 = Team::create([
        'name' => 'Bayern Munich',
        'power_level' => 90,
        'city' => 'Munich',
    ]);

    $team4 = Team::create([
        'name' => 'PSG',
        'power_level' => 88,
        'city' => 'Paris',
    ]);

    GameMatch::create([
        'home_team_id' => $team3->id,
        'away_team_id' => $team4->id,
        'week' => 1,
        'is_played' => false,
    ]);

    $results = $this->matchService->playWeek(1);

    expect($results)->toHaveCount(2)
        ->and($results[0]->is_played)->toBeTrue()
        ->and($results[1]->is_played)->toBeTrue();
});

test('play all matches processes all weeks', function () {
    // 2. hafta maçları ekle
    GameMatch::create([
        'home_team_id' => $this->team2->id,
        'away_team_id' => Team::create(['name' => 'Bayern', 'power_level' => 90, 'city' => 'Munich'])->id,
        'week' => 2,
        'is_played' => false,
    ]);

    GameMatch::create([
        'home_team_id' => Team::create(['name' => 'PSG', 'power_level' => 88, 'city' => 'Paris'])->id,
        'away_team_id' => $this->team1->id,
        'week' => 2,
        'is_played' => false,
    ]);

    $results = $this->matchService->playAllMatches();

    expect($results)->toHaveKeys([1, 2])
        ->and($results[1])->toHaveCount(1)
        ->and($results[2])->toHaveCount(2);
});

test('match result is realistic', function () {
    $results = [];

    // 100 kez simüle et ve sonuçları topla
    for ($i = 0; $i < 100; $i++) {
        $result = $this->matchService->simulateMatch($this->match);
        $results[] = $result['home_score'] + $result['away_score'];
    }

    $averageGoals = array_sum($results) / count($results);

    // Ortalama gol sayısı makul bir aralıkta olmalı (1.5 - 3.5)
    expect($averageGoals)->toBeGreaterThan(1.5)
        ->and($averageGoals)->toBeLessThan(3.5);
});

test('home team has advantage', function () {
    $homeWins = 0;
    $awayWins = 0;
    $draws = 0;

    // 100 kez simüle et
    for ($i = 0; $i < 100; $i++) {
        $result = $this->matchService->simulateMatch($this->match);

        if ($result['home_score'] > $result['away_score']) {
            $homeWins++;
        } elseif ($result['away_score'] > $result['home_score']) {
            $awayWins++;
        } else {
            $draws++;
        }
    }

    // Ev sahibi avantajı olmalı (daha fazla galibiyet)
    expect($homeWins)->toBeGreaterThan($awayWins);
});
