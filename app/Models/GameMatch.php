<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'week',
        'is_played',
    ];

    protected $casts = [
        'home_score' => 'integer',
        'away_score' => 'integer',
        'week' => 'integer',
        'is_played' => 'boolean',
    ];

    /**
     * Ev sahibi takım
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Deplasman takımı
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Maçın kazananını döndür
     */
    public function getWinner(): ?Team
    {
        if (! $this->is_played) {
            return null;
        }

        if ($this->home_score > $this->away_score) {
            return $this->homeTeam;
        }

        if ($this->away_score > $this->home_score) {
            return $this->awayTeam;
        }

        return null; // Beraberlik
    }

    /**
     * Maç beraberlik mi?
     */
    public function isDraw(): bool
    {
        return $this->is_played && $this->home_score === $this->away_score;
    }

    /**
     * Maç sonucu string olarak döndür
     */
    public function getResultString(): string
    {
        if (! $this->is_played) {
            return 'Henüz oynanmadı';
        }

        return "{$this->home_score} - {$this->away_score}";
    }
}
