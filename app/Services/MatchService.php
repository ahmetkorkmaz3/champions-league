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

        // Ev sahibi avantajı (%10)
        $homePower = $homeTeam->power_level * 1.1;
        $awayPower = $awayTeam->power_level;

        // Gol hesaplama
        $homeGoals = $this->calculateGoals($homePower, $awayPower);
        $awayGoals = $this->calculateGoals($awayPower, $homePower);

        return [
            'home_score' => $homeGoals,
            'away_score' => $awayGoals,
        ];
    }

    /**
     * Gol sayısını hesapla
     */
    private function calculateGoals(float $attackerPower, float $defenderPower): int
    {
        // Daha gerçekçi olasılık hesaplama
        $powerDifference = $attackerPower - $defenderPower;
        $baseChance = 0.12; // %12 temel gol atma şansı
        $powerBonus = ($powerDifference / 100) * 0.15; // Güç farkına göre daha büyük bonus
        $scoringChance = $baseChance + $powerBonus;

        // Sınırları belirle
        $scoringChance = max(0.05, min(0.30, $scoringChance)); // %5-%30 arası

        $goals = 0;
        $attempts = 7; // Maç başına ortalama 7 fırsat

        for ($i = 0; $i < $attempts; $i++) {
            if (random_int(1, 100) <= ($scoringChance * 100)) {
                $goals++;
            }
        }

        return min($goals, 4); // Maksimum 4 gol
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
        $maxWeek = GameMatch::max('week') ?? 6;

        for ($week = 1; $week <= $maxWeek; $week++) {
            $allResults[$week] = $this->playWeek($week);
        }

        return $allResults;
    }
}
