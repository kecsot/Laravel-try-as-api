<?php

namespace Database\Seeders;

use App\Models\Deck;
use Illuminate\Database\Seeder;

class DeckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Deck::factory()
            ->count(50)
            ->create();
    }
}
