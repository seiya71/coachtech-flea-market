<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            ['category_name' => 'ファッション'],
            ['category_name' => '家電'],
            ['category_name' => 'インテリア'],
            ['category_name' => 'レディース'],
            ['category_name' => 'メンズ'],
            ['category_name' => 'コスメ'],
            ['category_name' => '本'],
            ['category_name' => 'ゲーム'],
            ['category_name' => 'スポーツ'],
            ['category_name' => 'キッチン'],
            ['category_name' => 'ハンドメイド'],
            ['category_name' => 'アクセサリー'],
            ['category_name' => 'おもちゃ'],
            ['category_name' => 'ベビー・キッズ'],
        ]);
    }
}
