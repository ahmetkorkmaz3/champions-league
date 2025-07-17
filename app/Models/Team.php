<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'power_level',
        'logo',
        'city',
    ];

    protected $casts = [
        'power_level' => 'integer',
    ];

    /**
     * Takımın ev sahibi olduğu maçlar
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'home_team_id');
    }

    /**
     * Takımın deplasman olduğu maçlar
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'away_team_id');
    }

    /**
     * Takımın tüm maçları
     */
    public function matches()
    {
        return $this->homeMatches->merge($this->awayMatches);
    }

    /**
     * Takımın lig durumu
     */
    public function standing()
    {
        return $this->hasOne(LeagueStanding::class);
    }

    /**
     * Takımın güç seviyesine göre gol atma olasılığı
     */
    public function getGoalScoringProbability(): float
    {
        return $this->power_level / 100.0;
    }

    /**
     * Takımın güç seviyesine göre gol yeme olasılığı
     */
    public function getGoalConcedingProbability(): float
    {
        return (100 - $this->power_level) / 100.0;
    }
}
