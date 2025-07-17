<?php

namespace Database\Seeders;

use App\Models\GameMatch;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ChampionsLeagueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Takımları oluştur
        $teams = [
            [
                'name' => 'Real Madrid',
                'power_level' => 95,
                'city' => 'Madrid',
                'logo' => 'real-madrid.png',
            ],
            [
                'name' => 'Manchester City',
                'power_level' => 92,
                'city' => 'Manchester',
                'logo' => 'man-city.png',
            ],
            [
                'name' => 'Bayern Munich',
                'power_level' => 90,
                'city' => 'Munich',
                'logo' => 'bayern.png',
            ],
            [
                'name' => 'Paris Saint-Germain',
                'power_level' => 88,
                'city' => 'Paris',
                'logo' => 'psg.png',
            ],
        ];

        foreach ($teams as $teamData) {
            Team::create($teamData);
        }

        // Maçları oluştur (tek devreli lig - her takım bir kez karşılaşır)
        $matches = [
            // 1. Hafta
            ['home_team_id' => 1, 'away_team_id' => 2, 'week' => 1],
            ['home_team_id' => 3, 'away_team_id' => 4, 'week' => 1],

            // 2. Hafta
            ['home_team_id' => 2, 'away_team_id' => 3, 'week' => 2],
            ['home_team_id' => 4, 'away_team_id' => 1, 'week' => 2],

            // 3. Hafta
            ['home_team_id' => 1, 'away_team_id' => 3, 'week' => 3],
            ['home_team_id' => 2, 'away_team_id' => 4, 'week' => 3],
        ];

        foreach ($matches as $matchData) {
            GameMatch::create($matchData);
        }

        $this->command->info('Champions League takımları ve maçları oluşturuldu!');
    }
}
