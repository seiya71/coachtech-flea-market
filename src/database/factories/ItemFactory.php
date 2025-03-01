<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Item;
use App\Models\User;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_name' => $this->faker->word,
            'item_image' => $this->faker->imageUrl(),
            'sold' => $this->faker->boolean,
            'brand' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->numberBetween(1000, 10000),
            'condition' => $this->faker->randomElement(['new', 'used', 'refurbished']),
        ];
    }
}
