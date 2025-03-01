<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\Models\User::pluck('id')->toArray();

        $items = [
            [
                'item_name' => '腕時計',
                'brand' => 'エンポリオ・アルマーニ',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000,
                'condition' => '良好',
            ],
            [
                'item_name' => 'HDD',
                'brand' => '東芝',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000,
                'condition' => '目立った傷や汚れなし',
            ],
            [
                'item_name' => '玉ねぎ3束',
                'brand' => '兵庫県',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300,
                'condition' => 'やや傷や汚れあり',
            ],
            [
                'item_name' => '革靴',
                'brand' => 'ジョンロブ',
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000,
                'condition' => '状態が悪い',
            ],
            [
                'item_name' => 'ノートPC',
                'brand' => '富士通',
                'description' => '高性能なノートパソコン',
                'price' => 45000,
                'condition' => '良好',
            ],
            [
                'item_name' => 'マイク',
                'brand' => 'SONY',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000,
                'condition' => '目立った傷や汚れなし',
            ],
            [
                'item_name' => 'ショルダーバッグ',
                'brand' => 'NNEWEST',
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500,
                'condition' => 'やや傷や汚れあり',
            ],
            [
                'item_name' => 'タンブラー',
                'brand' => 'アトラス',
                'description' => '使いやすいタンブラー',
                'price' => 500,
                'condition' => '状態が悪い',
            ],
            [
                'item_name' => 'コーヒーミル',
                'brand' => '河野',
                'description' => '手動のコーヒーミル',
                'price' => 4000,
                'condition' => '良好',
            ],
            [
                'item_name' => 'メイクセット',
                'brand' => '資生堂',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'condition' => '目立った傷や汚れなし',
            ]
        ];

        $itemsForInsert = [];

        for ($i = 0; $i < 3; $i++) {
            foreach ($items as $item) {
                $imageName = $item['item_name'] . '.png';

                if (Storage::disk('public')->exists('item_images/' . $imageName)) {
                    $imageUrl = 'item_images/' . $imageName;
                } else {
                    $imageUrl = null;
                }

                $itemsForInsert[] = [
                    'item_image' => $imageUrl,
                    'item_name' => $item['item_name'],
                    'brand' => $item['brand'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'user_id' => $users[array_rand($users)],
                    'condition' => $item['condition'],
                    'sold' => rand(1, 10) <= 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('items')->insert($itemsForInsert);
    }
}
