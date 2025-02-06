<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'is_primary' => false,
            'state' => $this->faker->region(),
            'city' => $this->faker->city(),
            'street' => $this->faker->streetAddress(),
            'house' => $this->faker->numberBetween(1, 128),
            'postal_code' => $this->faker->postcode(),
            'apartment' => $this->faker->buildingNumber(),
        ];
    }
}
