<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;

class ItemCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            ['category_id' => 1, 'item_id' => 1],
            ['category_id' => 5, 'item_id' => 1],
            ['category_id' => 15, 'item_id' => 2],
            ['category_id' => 16, 'item_id' => 2],
            ['category_id' => 10, 'item_id' => 3],
            ['category_id' => 1, 'item_id' => 4],
            ['category_id' => 5, 'item_id' => 4],
            ['category_id' => 15, 'item_id' => 5],
            ['category_id' => 16, 'item_id' => 5],
            ['category_id' => 16, 'item_id' => 6],
            ['category_id' => 1, 'item_id' => 7],
            ['category_id' => 4, 'item_id' => 7],
            ['category_id' => 10, 'item_id' => 8],
            ['category_id' => 10, 'item_id' => 9],
            ['category_id' => 1, 'item_id' => 10],
            ['category_id' => 4, 'item_id' => 10],
            ['category_id' => 6, 'item_id' => 10],
        ];

        $insertData = [];
        foreach (range(0, 2) as $set) {
            $offset = $set * 10;
            $insertData = array_merge($insertData, array_map(function ($item) use ($offset) {
                return [
                    'category_id' => $item['category_id'],
                    'item_id' => $item['item_id'] + $offset,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $items));
        }

        DB::table('item_category')->insert($insertData);
    }
}
