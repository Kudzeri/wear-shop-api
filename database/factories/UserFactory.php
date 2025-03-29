<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'avatar_url' => 'https://www.ergo-style.ru/dileram/ergostyle.com.ua/images_new/dostavka_i_oplata/manager.png',
            'phone' => $this->faker->numerify('+7 (###) ###-##-##'),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ];
    }
}
