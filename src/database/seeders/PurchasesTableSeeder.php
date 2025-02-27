<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\User;
use App\Models\Address;

use Illuminate\Database\Seeder;

class PurchasesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $soldItems = \App\Models\Item::where('sold', true)->pluck('id');

        $userIds = \App\Models\User::pluck('id');

        if ($userIds->isEmpty()) {
            \App\Models\User::factory(10)->create();
            $userIds = \App\Models\User::pluck('id');
        }

        foreach ($soldItems as $itemId) {
            $userId = $userIds->random();

            $address = \App\Models\Address::firstOrCreate(
                ['user_id' => $userId],
                [
                    'postal_code' => sprintf("%03d-%04d", rand(100, 999), rand(1000, 9999)),
                    'address' => '東京都渋谷区' . rand(1, 20) . '-' . rand(1, 20) . '-' . rand(1, 20),
                    'building_name' => 'マンション' . rand(101, 999),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            \App\Models\Purchase::create([
                'user_id' => $userId,
                'item_id' => $itemId,
                'address_id' => $address->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

}
