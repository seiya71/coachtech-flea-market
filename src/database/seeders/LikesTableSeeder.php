<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

use Illuminate\Database\Seeder;

class LikesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $likesCount = 200;

        $insertData = [];
        while (count($insertData) < $likesCount) {
            $user_id = $faker->numberBetween(1, 100);
            $item_id = $faker->numberBetween(1, 30);

            while (DB::table('items')->where('id', $item_id)->value('user_id') == $user_id) {
                $user_id = $faker->numberBetween(1, 100);
            }

            // 同じユーザーが同じ商品に二重にいいねしないようにする
            $key = $user_id . '-' . $item_id;
            if (isset($insertData[$key])) {
                continue;
            }

            $insertData[$key] = [
                'user_id' => $user_id,
                'item_id' => $item_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('likes')->insert(array_values($insertData));
    }
}
