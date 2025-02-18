<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('ja_JP');
        $images = [
            'profile_images/kkrn_icon_user_6.png',
            'profile_images/kkrn_icon_user_7.png',
            'profile_images/kkrn_icon_user_8.png',
            'profile_images/kkrn_icon_user_9.png',
            'profile_images/kkrn_icon_user_10.png',
        ];

        foreach (range(1, 100) as $index) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('12345678'),
                'profile_image' => $images[array_rand($images)],
                'postal_code' => $faker->postcode,
                'address' => $faker->address,
                'building_name' => $faker->secondaryAddress,
                'email_verified_at' => now(),
                'first_login' => false,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
