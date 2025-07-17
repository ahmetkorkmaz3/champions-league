<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameMatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'week' => $this->week,
            'is_played' => $this->is_played,
            'home_score' => $this->home_score,
            'away_score' => $this->away_score,
            'home_team' => TeamResource::make($this->whenLoaded('homeTeam')),
            'away_team' => TeamResource::make($this->whenLoaded('awayTeam')),
            'result_string' => $this->getResultString(),
            'winner' => $this->when($this->is_played, function () {
                $winner = $this->getWinner();

                return $winner ? TeamResource::make($winner) : null;
            }),
            'is_draw' => $this->when($this->is_played, $this->isDraw()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
