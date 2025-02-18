<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\User;

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
        $soldItems = Item::where('sold', true)->get();

        foreach ($soldItems as $item) {
            $user_id = User::where('id', '!=', $item->user_id)
                ->inRandomOrder()
                ->first()
                ->id;

            DB::table('purchases')->insert([
                'user_id' => $user_id,
                'item_id' => $item->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
