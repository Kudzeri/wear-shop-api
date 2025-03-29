<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Address;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory(10)->create()->each(function (User $user) {
            $addresses = Address::inRandomOrder()->limit(rand(1, 3))->get();
            $user->addresses()->attach($addresses);

            if ($addresses->isNotEmpty()) {
                $user->addresses()->updateExistingPivot($addresses->first()->id, ['is_primary' => true]);
            }
        });
    }
}
