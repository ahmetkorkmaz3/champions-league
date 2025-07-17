<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Models\Team;
use Illuminate\Support\Collection;

class LeagueService
{
    /**
     * Belirli bir maçın etkilediği takımların lig durumunu güncelle
     */
    public function updateStandingsForMatch(GameMatch $match): void
    {
        // Maçın etkilediği takımları güncelle
        $this->updateTeamStanding($match->homeTeam);
        $this->updateTeamStanding($match->awayTeam);

        // Sıralamaları güncelle
        $this->updatePositions();
    }

    /**
     * Lig tablosunu güncelle
     */
    public function updateStandings(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            $this->updateTeamStanding($team);
        }

        $this->updatePositions();
    }

    /**
     * Belirli bir takımın lig durumunu güncelle
     */
    public function updateTeamStanding(Team $team): void
    {
        // Önce takımın lig durumunu sıfırla
        LeagueStanding::updateOrCreate(
            ['team_id' => $team->id],
            [
                'points' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
            ]
        );

        $homeMatches = $team->homeMatches()->where('is_played', true)->get();
        $awayMatches = $team->awayMatches()->where('is_played', true)->get();

        $wins = 0;
        $draws = 0;
        $losses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;

        // Ev sahibi maçları
        foreach ($homeMatches as $match) {
            if ($match->home_score > $match->away_score) {
                $wins++;
            } elseif ($match->home_score === $match->away_score) {
                $draws++;
            } else {
                $losses++;
            }

            $goalsFor += $match->home_score;
            $goalsAgainst += $match->away_score;
        }

        // Deplasman maçları
        foreach ($awayMatches as $match) {
            if ($match->away_score > $match->home_score) {
                $wins++;
            } elseif ($match->away_score === $match->home_score) {
                $draws++;
            } else {
                $losses++;
            }

            $goalsFor += $match->away_score;
            $goalsAgainst += $match->home_score;
        }

        $points = ($wins * 3) + $draws;
        $goalDifference = $goalsFor - $goalsAgainst;

        // Lig durumunu güncelle
        LeagueStanding::where('team_id', $team->id)->update([
            'points' => $points,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'goal_difference' => $goalDifference,
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
        ]);
    }

    /**
     * Sıralamaları güncelle
     */
    public function updatePositions(): void
    {
        $standings = LeagueStanding::with('team')
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->orderBy('goals_for', 'desc')
            ->orderBy('team_id', 'asc')
            ->get();

        foreach ($standings as $index => $standing) {
            $standing->update(['position' => $index + 1]);
        }
    }

    /**
     * Güncel lig tablosunu getir
     */
    public function getCurrentStandings(): Collection
    {
        return LeagueStanding::with('team')
            ->orderBy('position', 'asc')
            ->get();
    }

    /**
     * Belirli bir haftadan sonraki tahmini lig tablosu
     */
    public function getPredictedStandings(int $afterWeek = 3): array
    {
        // O haftaya kadar oynanan maçları al
        $playedMatches = GameMatch::where('week', '<=', $afterWeek)
            ->where('is_played', true)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        // Kalan maçları simüle et
        $remainingMatches = GameMatch::where('week', '>', $afterWeek)
            ->where('is_played', false)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        // Geçici takım durumları oluştur
        $tempStandings = [];
        $teams = Team::all();

        foreach ($teams as $team) {
            $tempStandings[$team->id] = [
                'team' => $team,
                'points' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
            ];
        }

        // Oynanan maçları hesapla
        foreach ($playedMatches as $match) {
            $this->addMatchToStandings($tempStandings, $match);
        }

        // Kalan maçları simüle et
        $matchService = new MatchService;
        foreach ($remainingMatches as $match) {
            $simulatedResult = $matchService->simulateMatch($match);

            // Simüle edilmiş sonucu geçici maç objesine ekle
            $match->home_score = $simulatedResult['home_score'];
            $match->away_score = $simulatedResult['away_score'];
            $match->is_played = true;

            $this->addMatchToStandings($tempStandings, $match);
        }

        // Sıralama yap
        $sortedStandings = collect($tempStandings)->sortByDesc(function ($standing) {
            return [
                $standing['points'],
                $standing['goals_for'] - $standing['goals_against'],
                $standing['goals_for'],
                $standing['team']->name,
            ];
        })->values();

        return $sortedStandings->map(function ($standing, $index) {
            return [
                'position' => $index + 1,
                'team' => $standing['team'],
                'points' => $standing['points'],
                'goals_for' => $standing['goals_for'],
                'goals_against' => $standing['goals_against'],
                'goal_difference' => $standing['goals_for'] - $standing['goals_against'],
                'wins' => $standing['wins'],
                'draws' => $standing['draws'],
                'losses' => $standing['losses'],
                'matches_played' => $standing['wins'] + $standing['draws'] + $standing['losses'],
            ];
        })->toArray();
    }

    /**
     * Maç sonucunu geçici tabloya ekle
     */
    private function addMatchToStandings(array &$standings, GameMatch $match): void
    {
        $homeTeamId = $match->home_team_id;
        $awayTeamId = $match->away_team_id;

        // Ev sahibi takım
        $standings[$homeTeamId]['goals_for'] += $match->home_score;
        $standings[$homeTeamId]['goals_against'] += $match->away_score;

        // Deplasman takımı
        $standings[$awayTeamId]['goals_for'] += $match->away_score;
        $standings[$awayTeamId]['goals_against'] += $match->home_score;

        if ($match->home_score > $match->away_score) {
            // Ev sahibi kazandı
            $standings[$homeTeamId]['wins']++;
            $standings[$homeTeamId]['points'] += 3;
            $standings[$awayTeamId]['losses']++;
        } elseif ($match->away_score > $match->home_score) {
            // Deplasman kazandı
            $standings[$awayTeamId]['wins']++;
            $standings[$awayTeamId]['points'] += 3;
            $standings[$homeTeamId]['losses']++;
        } else {
            // Beraberlik
            $standings[$homeTeamId]['draws']++;
            $standings[$homeTeamId]['points'] += 1;
            $standings[$awayTeamId]['draws']++;
            $standings[$awayTeamId]['points'] += 1;
        }
    }

    /**
     * Play all matches
     */
    public function playAllMatches(): void
    {
        $unplayedMatches = GameMatch::where('is_played', false)->get();
        $matchService = new \App\Services\MatchService;

        foreach ($unplayedMatches as $match) {
            $result = $matchService->simulateMatch($match);
            $match->update([
                'home_score' => $result['home_score'],
                'away_score' => $result['away_score'],
                'is_played' => true,
            ]);
        }

        $this->updateStandings();
    }

    /**
     * Play matches for a specific week
     */
    public function playWeek(int $week): void
    {
        $weekMatches = GameMatch::where('week', $week)
            ->where('is_played', false)
            ->get();

        $matchService = new \App\Services\MatchService;

        foreach ($weekMatches as $match) {
            $result = $matchService->simulateMatch($match);
            $match->update([
                'home_score' => $result['home_score'],
                'away_score' => $result['away_score'],
                'is_played' => true,
            ]);
        }

        $this->updateStandings();
    }

    public function regenerateMatches(): void
    {
        // Delete all existing matches
        GameMatch::query()->delete();

        // Get all teams
        $teams = Team::all();
        $teamIds = $teams->pluck('id')->toArray();

        // Create double round-robin tournament (çift devreli lig)
        $matches = [];

        // If odd number of teams, add a "bye" team (null)
        if (count($teamIds) % 2 != 0) {
            $teamIds[] = null;
        }

        $numTeams = count($teamIds);
        $numWeeks = ($numTeams - 1) * 2; // Çift devreli lig için 2 katı
        $halfSize = $numTeams / 2;

        // Create a copy of team IDs for rotation
        $rotatingTeams = $teamIds;

        // Generate double round-robin schedule using circle method
        for ($week = 1; $week <= $numWeeks; $week++) {
            $weekMatches = [];

            for ($i = 0; $i < $halfSize; $i++) {
                $team1 = $rotatingTeams[$i];
                $team2 = $rotatingTeams[$numTeams - 1 - $i];

                // Skip if one of the teams is null (bye)
                if ($team1 !== null && $team2 !== null) {
                    // İkinci devrede ev sahibi/deplasman rollerini değiştir
                    if ($week > ($numWeeks / 2)) {
                        $temp = $team1;
                        $team1 = $team2;
                        $team2 = $temp;
                    }

                    $weekMatches[] = [
                        'home_team_id' => $team1,
                        'away_team_id' => $team2,
                        'week' => $week,
                        'home_score' => null,
                        'away_score' => null,
                        'is_played' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            $matches = array_merge($matches, $weekMatches);

            // Rotate teams for next week (keep first team fixed, rotate others)
            if ($week < $numWeeks) {
                $lastTeam = array_pop($rotatingTeams);
                array_splice($rotatingTeams, 1, 0, $lastTeam);
            }
        }

        // Insert all matches
        GameMatch::insert($matches);
    }
}
