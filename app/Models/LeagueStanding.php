<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStanding extends Model
{
    protected $fillable = [
        'team_id',
        'points',
        'goals_for',
        'goals_against',
        'goal_difference',
        'wins',
        'draws',
        'losses',
        'position',
    ];

    protected $casts = [
        'points' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'goal_difference' => 'integer',
        'wins' => 'integer',
        'draws' => 'integer',
        'losses' => 'integer',
        'position' => 'integer',
    ];

    /**
     * Takım
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Oynanan maç sayısı
     */
    public function getMatchesPlayed(): int
    {
        return $this->wins + $this->draws + $this->losses;
    }

    /**
     * Gol farkını hesapla
     */
    public function calculateGoalDifference(): int
    {
        return $this->goals_for - $this->goals_against;
    }

    /**
     * Puanları hesapla
     */
    public function calculatePoints(): int
    {
        return ($this->wins * 3) + $this->draws;
    }

    /**
     * Sıralama için kriterleri döndür (sıralama için kullanılacak)
     */
    public function getSortingCriteria(): array
    {
        return [
            'points' => $this->points,
            'goal_difference' => $this->goal_difference,
            'goals_for' => $this->goals_for,
            'team_name' => $this->team->name,
        ];
    }
}
