<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Item;

class PurchaseFactory extends Factory
{
    protected $purchase = Purchase::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            'shipping_postal_code' => $this->faker->postcode,
            'shipping_address' => $this->faker->address,
            'shipping_building_name' => $this->faker->secondaryAddress,
        ];
    }
}
