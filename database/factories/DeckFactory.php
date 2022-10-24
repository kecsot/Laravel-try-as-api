<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeckFactory extends Factory
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
            'description' => $this->faker->realTextBetween(40,250),
            'owner_id' => User::all()->random()->id,
        ];
    }
}
