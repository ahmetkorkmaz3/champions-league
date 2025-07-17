<?php

namespace App\Services;

use App\Models\GameMatch;

class MatchService
{
    /**
     * Maç sonucunu simüle et
     */
    public function simulateMatch(GameMatch $match): array
    {
        $homeTeam = $match->homeTeam;
        $awayTeam = $match->awayTeam;

        // Ev sahibi avantajı (+10% güç)
        $homeTeamPower = $homeTeam->power_level * 1.1;
        $awayTeamPower = $awayTeam->power_level;

        // Gol atma olasılıkları
        $homeScoringProb = $homeTeamPower / 100;
        $awayScoringProb = $awayTeamPower / 100;

        // Maç süresi boyunca gol atma şansları (90 dakika)
        $homeGoals = $this->calculateGoals($homeScoringProb, $awayTeam->getGoalConcedingProbability());
        $awayGoals = $this->calculateGoals($awayScoringProb, $homeTeam->getGoalConcedingProbability());

        return [
            'home_score' => $homeGoals,
            'away_score' => $awayGoals,
        ];
    }

    /**
     * Gol sayısını hesapla
     */
    private function calculateGoals(float $scoringProb, float $opponentConcedingProb): int
    {
        $totalProb = ($scoringProb + $opponentConcedingProb) / 2;

        // Poisson dağılımı kullanarak daha gerçekçi gol sayıları
        $expectedGoals = $totalProb * 2.5; // Ortalama 2.5 gol per maç

        $goals = 0;
        for ($i = 0; $i < 10; $i++) { // 10 fırsat
            if (random_int(1, 100) <= ($expectedGoals * 10)) {
                $goals++;
            }
        }

        return min($goals, 5); // Maksimum 5 gol
    }

    /**
     * Maçı oynat ve sonucu kaydet
     */
    public function playMatch(GameMatch $match): GameMatch
    {
        $result = $this->simulateMatch($match);

        $match->update([
            'home_score' => $result['home_score'],
            'away_score' => $result['away_score'],
            'is_played' => true,
        ]);

        return $match->fresh();
    }

    /**
     * Belirli bir haftadaki tüm maçları oynat
     */
    public function playWeek(int $week): array
    {
        $matches = GameMatch::where('week', $week)
            ->where('is_played', false)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        $results = [];
        foreach ($matches as $match) {
            $results[] = $this->playMatch($match);
        }

        return $results;
    }

    /**
     * Tüm maçları oynat
     */
    public function playAllMatches(): array
    {
        $allResults = [];

        for ($week = 1; $week <= 6; $week++) {
            $weekResults = $this->playWeek($week);
            $allResults[$week] = $weekResults;
        }

        return $allResults;
    }
}
