<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Address;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'postal_code' => $this->faker->postcode,
            'address' => $this->faker->address,
            'building_name' => $this->faker->secondaryAddress,
        ];
    }
}
