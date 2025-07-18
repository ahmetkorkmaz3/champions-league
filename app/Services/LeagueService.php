<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\LeagueStanding;
use App\Models\Team;
use Illuminate\Support\Collection;

class LeagueService
{
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
     * Belirli bir maçın etkilediği takımların lig durumunu güncelle
     */
    public function updateStandingsForMatch(GameMatch $match): void
    {
        $this->updateTeamStanding($match->homeTeam);
        $this->updateTeamStanding($match->awayTeam);
        $this->updatePositions();
    }

    /**
     * Belirli bir takımın lig durumunu güncelle
     */
    public function updateTeamStanding(Team $team): void
    {
        $stats = $this->calculateTeamStats($team);

        LeagueStanding::updateOrCreate(
            ['team_id' => $team->id],
            [
                'points' => $stats['points'],
                'goals_for' => $stats['goals_for'],
                'goals_against' => $stats['goals_against'],
                'goal_difference' => $stats['goal_difference'],
                'wins' => $stats['wins'],
                'draws' => $stats['draws'],
                'losses' => $stats['losses'],
            ]
        );
    }

    /**
     * Takım istatistiklerini hesapla
     */
    private function calculateTeamStats(Team $team): array
    {
        $homeMatches = $team->homeMatches()->where('is_played', true)->get();
        $awayMatches = $team->awayMatches()->where('is_played', true)->get();

        $wins = 0;
        $draws = 0;
        $losses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;

        // Ev sahibi maçları
        foreach ($homeMatches as $match) {
            $goalsFor += $match->home_score;
            $goalsAgainst += $match->away_score;

            if ($match->home_score > $match->away_score) {
                $wins++;
            } elseif ($match->home_score === $match->away_score) {
                $draws++;
            } else {
                $losses++;
            }
        }

        // Deplasman maçları
        foreach ($awayMatches as $match) {
            $goalsFor += $match->away_score;
            $goalsAgainst += $match->home_score;

            if ($match->away_score > $match->home_score) {
                $wins++;
            } elseif ($match->away_score === $match->home_score) {
                $draws++;
            } else {
                $losses++;
            }
        }

        return [
            'points' => ($wins * 3) + $draws,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'goal_difference' => $goalsFor - $goalsAgainst,
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
        ];
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
        $playedMatches = GameMatch::where('week', '<=', $afterWeek)
            ->where('is_played', true)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        $remainingMatches = GameMatch::where('week', '>', $afterWeek)
            ->where('is_played', false)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        $tempStandings = $this->initializeTempStandings();

        // Oynanan maçları hesapla
        foreach ($playedMatches as $match) {
            $this->addMatchToStandings($tempStandings, $match);
        }

        // Kalan maçları simüle et
        $matchService = new MatchService;
        foreach ($remainingMatches as $match) {
            $result = $matchService->simulateMatch($match);
            $match->home_score = $result['home_score'];
            $match->away_score = $result['away_score'];
            $this->addMatchToStandings($tempStandings, $match);
        }

        return $this->sortAndFormatStandings($tempStandings);
    }

    /**
     * Şampiyonluk olasılıklarını hesapla
     */
    public function calculateChampionshipProbabilities(int $currentWeek = 0): array
    {
        $standings = $this->getCurrentStandings();
        $totalTeams = $standings->count();
        $maxPoints = 18; // 6 maç x 3 puan
        $remainingMatches = 6 - $currentWeek;
        
        if ($totalTeams === 0) {
            return [];
        }

        $probabilities = [];
        
        foreach ($standings as $index => $standing) {
            $team = $standing->team;
            $currentPoints = $standing->points;
            $powerLevel = $team->power_level ?? 50;
            
            // Temel faktörler - daha dengeli ağırlıklar
            $pointsFactor = $currentPoints / $maxPoints; // Mevcut puanların maksimum puana oranı
            $positionFactor = max(0, 1 - ($index / ($totalTeams * 0.7))); // Pozisyon faktörü (daha yumuşak)
            $powerFactor = $powerLevel / 100; // Güç seviyesi faktörü
            
            // Kalan maçlar için potansiyel puan hesaplama
            $remainingPotentialPoints = $remainingMatches * 3;
            $maxPossiblePoints = $currentPoints + $remainingPotentialPoints;
            
            // Diğer takımların maksimum puanlarını kontrol et
            $otherTeamsMaxPoints = $standings->map(function ($s) use ($remainingPotentialPoints) {
                return $s->points + $remainingPotentialPoints;
            })->toArray();
            
            $maxCompetitorPoints = max(array_filter($otherTeamsMaxPoints, function ($key) use ($index) {
                return $key !== $index;
            }, ARRAY_FILTER_USE_KEY));
            
            // Eğer bu takım diğerlerinden daha fazla puan alabilirse şansı var
            $canWin = $maxPossiblePoints >= $maxCompetitorPoints;
            
            if (!$canWin) {
                $probabilities[] = 0;
                continue;
            }
            
            // Şampiyonluk olasılığı hesaplama - daha dengeli ağırlıklar
            $probability = 0;
            
            // Mevcut durum faktörü (%35 ağırlık) - azaltıldı
            $probability += ($pointsFactor * 0.35);
            
            // Pozisyon faktörü (%25 ağırlık) - azaltıldı
            $probability += ($positionFactor * 0.25);
            
            // Güç seviyesi faktörü (%25 ağırlık) - artırıldı
            $probability += ($powerFactor * 0.25);
            
            // Kalan maç avantajı (%15 ağırlık) - artırıldı
            $remainingAdvantage = $remainingMatches / 6;
            $probability += ($remainingAdvantage * 0.15);
            
            // Belirsizlik faktörü - kalan maç sayısına göre belirsizlik ekle
            $uncertaintyFactor = min(0.3, $remainingMatches * 0.05); // Kalan her maç için %5 belirsizlik
            $probability *= (1 - $uncertaintyFactor);
            
            // Normalize et (0-100 arası)
            $probability = round($probability * 100);
            
            $probabilities[] = max(0, min(100, $probability));
        }
        
        // Toplam olasılığı 100'e normalize et
        $totalProbability = array_sum($probabilities);
        if ($totalProbability > 0) {
            $probabilities = array_map(function ($prob) use ($totalProbability) {
                return round(($prob / $totalProbability) * 100);
            }, $probabilities);
        }
        
        return $probabilities;
    }

    /**
     * Geçici tablo başlat
     */
    private function initializeTempStandings(): array
    {
        $standings = [];
        $teams = Team::all();

        foreach ($teams as $team) {
            $standings[$team->id] = [
                'team' => $team,
                'points' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
            ];
        }

        return $standings;
    }

    /**
     * Maç sonucunu geçici tabloya ekle
     */
    private function addMatchToStandings(array &$standings, GameMatch $match): void
    {
        $homeId = $match->home_team_id;
        $awayId = $match->away_team_id;

        $standings[$homeId]['goals_for'] += $match->home_score;
        $standings[$homeId]['goals_against'] += $match->away_score;
        $standings[$awayId]['goals_for'] += $match->away_score;
        $standings[$awayId]['goals_against'] += $match->home_score;

        if ($match->home_score > $match->away_score) {
            $standings[$homeId]['wins']++;
            $standings[$homeId]['points'] += 3;
            $standings[$awayId]['losses']++;
        } elseif ($match->away_score > $match->home_score) {
            $standings[$awayId]['wins']++;
            $standings[$awayId]['points'] += 3;
            $standings[$homeId]['losses']++;
        } else {
            $standings[$homeId]['draws']++;
            $standings[$homeId]['points'] += 1;
            $standings[$awayId]['draws']++;
            $standings[$awayId]['points'] += 1;
        }
    }

    /**
     * Tabloyu sırala ve formatla
     */
    private function sortAndFormatStandings(array $standings): array
    {
        $sorted = collect($standings)->sortByDesc(function ($standing) {
            return [
                $standing['points'],
                $standing['goals_for'] - $standing['goals_against'],
                $standing['goals_for'],
                $standing['team']->name,
            ];
        })->values();

        return $sorted->map(function ($standing, $index) {
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
     * Tüm maçları oynat
     */
    public function playAllMatches(): void
    {
        $matchService = new MatchService;
        $maxWeek = GameMatch::max('week') ?? 6;

        for ($week = 1; $week <= $maxWeek; $week++) {
            $matchService->playWeek($week);
        }

        $this->updateStandings();
    }

    /**
     * Belirli bir haftayı oynat
     */
    public function playWeek(int $week): void
    {
        $matchService = new MatchService;
        $matchService->playWeek($week);
        $this->updateStandings();
    }

    /**
     * Maçları yeniden oluştur
     */
    public function regenerateMatches(): void
    {
        GameMatch::truncate();

        $teams = Team::all()->pluck('id')->toArray();
        $teamCount = count($teams);

        $weeks = ($teamCount - 1) * 2; // Çift devreli
        $half = $teamCount / 2;

        $schedule = [];

        // Takımları sabit ve dönen olarak ayır
        $fixed = $teams[0];
        $rotating = array_slice($teams, 1);

        // İlk devre
        for ($week = 0; $week < $teamCount - 1; $week++) {
            $pairings = [];
            $pairings[] = [$fixed, $rotating[$week % ($teamCount - 1)]];

            for ($i = 1; $i < $half; $i++) {
                $home = $rotating[($week + $i) % ($teamCount - 1)];
                $away = $rotating[($week - $i + $teamCount - 1) % ($teamCount - 1)];
                $pairings[] = [$home, $away];
            }

            foreach ($pairings as $pair) {
                $schedule[] = [
                    'home_team_id' => $pair[0],
                    'away_team_id' => $pair[1],
                    'week' => $week + 1,
                    'is_played' => false,
                ];
            }
        }

        // İkinci devre (ev-deplasman değişiyor)
        for ($week = 0; $week < $teamCount - 1; $week++) {
            $pairings = [];
            $pairings[] = [$rotating[$week % ($teamCount - 1)], $fixed];

            for ($i = 1; $i < $half; $i++) {
                $home = $rotating[($week - $i + $teamCount - 1) % ($teamCount - 1)];
                $away = $rotating[($week + $i) % ($teamCount - 1)];
                $pairings[] = [$home, $away];
            }

            foreach ($pairings as $pair) {
                $schedule[] = [
                    'home_team_id' => $pair[0],
                    'away_team_id' => $pair[1],
                    'week' => $week + 1 + ($teamCount - 1),
                    'is_played' => false,
                ];
            }
        }

        foreach ($schedule as $matchData) {
            GameMatch::create($matchData);
        }
    }

    /**
     * Maçları sıfırla ve lig tablosunu temizle
     */
    public function resetMatches(): void
    {
        $this->regenerateMatches();

        LeagueStanding::query()->update([
            'points' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'position' => 0,
        ]);
    }

    /**
     * Tüm maçları getir (haftalara göre gruplandırılmış)
     */
    public function getAllMatchesGroupedByWeek(): \Illuminate\Support\Collection
    {
        $matches = GameMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('week')
            ->orderBy('id')
            ->get();

        return $matches->groupBy('week');
    }

    /**
     * Maç silindikten sonra etkilenen takımları güncelle
     */
    public function handleMatchDeletion(GameMatch $match): void
    {
        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;

        $this->updateTeamStanding($homeTeam);
        $this->updateTeamStanding($awayTeam);
        $this->updatePositions();
    }
}
