<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

use Illuminate\Database\Seeder;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $comments = [
            '素晴らしい商品ですね！',
            'とても良い感じです。',
            '期待していた通りの商品でした。',
            '商品にとても満足しています。',
            'とても使いやすそうです！',
        ];

        $commentsCount = 200;

        $insertData = [];
        for ($i = 0; $i < $commentsCount; $i++) {
            $user_id = $faker->numberBetween(1, 100);
            $item_id = $faker->numberBetween(1, 30);

            while (DB::table('items')->where('id', $item_id)->value('user_id') == $user_id) {
                $user_id = $faker->numberBetween(1, 100);
            }

            $comment = $faker->randomElement($comments);

            $insertData[] = [
                'user_id' => $user_id,
                'item_id' => $item_id,
                'content' => $comment,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('comments')->insert($insertData);

    }
}
