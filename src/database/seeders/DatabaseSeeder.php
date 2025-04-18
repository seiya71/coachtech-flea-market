<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CategoriesTableSeeder::class,
            UsersTableSeeder::class,
            ItemsTableSeeder::class,
            ItemCategoryTableSeeder::class,
            LikesTableSeeder::class,
            CommentsTableSeeder::class,
            AddressesTableSeeder::class,
            PurchasesTableSeeder::class,
        ]);
    }
}
