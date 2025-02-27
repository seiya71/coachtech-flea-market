<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Purchase;

class AddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = Purchase::distinct()->pluck('user_id');
        dd($userIds);

        foreach ($userIds as $userId) {
            DB::table('addresses')->insert([
                'user_id' => $userId,
                'postal_code' => sprintf("%03d-%04d", rand(100, 999), rand(1000, 9999)), // 例: 123-4567
                'address' => '東京都渋谷区' . rand(1, 20) . '-' . rand(1, 20) . '-' . rand(1, 20),
                'building_name' => 'マンション' . rand(101, 999),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
