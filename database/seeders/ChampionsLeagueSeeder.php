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
                'name' => 'Arsenal',
                'power_level' => 95,
                'city' => 'London',
                'logo' => 'https://resources.premierleague.com/premierleague25/badges/3.svg',
            ],
            [
                'name' => 'Chelsea',
                'power_level' => 92,
                'city' => 'London',
                'logo' => 'https://resources.premierleague.com/premierleague25/badges/8.svg',
            ],
            [
                'name' => 'Liverpool',
                'power_level' => 90,
                'city' => 'Liverpool',
                'logo' => 'https://resources.premierleague.com/premierleague25/badges/14.svg',
            ],
            [
                'name' => 'Manchester United',
                'power_level' => 88,
                'city' => 'Trafford',
                'logo' => 'https://resources.premierleague.com/premierleague25/badges/1.svg',
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
