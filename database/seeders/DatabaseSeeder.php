<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Champions League verilerini ekle
        $this->call([
            ChampionsLeagueSeeder::class,
        ]);
    }
}
