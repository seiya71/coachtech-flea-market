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
            ['category_name' => 'ファッション'],//1
            ['category_name' => '家電'],//2
            ['category_name' => 'インテリア'],//3
            ['category_name' => 'レディース'],//4
            ['category_name' => 'メンズ'],//5
            ['category_name' => 'コスメ'],//6
            ['category_name' => '本'],//7
            ['category_name' => 'ゲーム'],//8
            ['category_name' => 'スポーツ'],//9
            ['category_name' => 'キッチン'],//10
            ['category_name' => 'ハンドメイド'],//11
            ['category_name' => 'アクセサリー'],//12
            ['category_name' => 'おもちゃ'],//13
            ['category_name' => 'ベビー・キッズ'],//14
            ['category_name' => 'PC'],//15
            ['category_name' => '電子機器'],//16
        ]);
    }
}
