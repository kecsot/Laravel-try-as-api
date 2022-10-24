<?php

namespace Database\Factories;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'deck_id' => Deck::all()->random()->id, // TODO:
            'owner_id' => User::all()->random()->id,
        ];
    }

}
