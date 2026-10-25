<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddShippingAddressToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 住所を変更しても過去の購入の配送先が変わらないように、購入時の住所を購入データに保存する
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('shipping_postal_code')->nullable()->after('item_id');
            $table->string('shipping_address')->nullable()->after('shipping_postal_code');
            $table->string('shipping_building_name')->nullable()->after('shipping_address');
        });

        // 既存の購入は、今登録されている住所を配送先としてコピーする
        DB::table('purchases')
            ->join('addresses', 'addresses.id', '=', 'purchases.address_id')
            ->update([
                'purchases.shipping_postal_code' => DB::raw('addresses.postal_code'),
                'purchases.shipping_address' => DB::raw('addresses.address'),
                'purchases.shipping_building_name' => DB::raw('addresses.building_name'),
            ]);

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
            $table->dropColumn('address_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('address_id')->nullable()->after('item_id')->constrained();
        });

        // 購入時の住所は元に戻せないので、購入者の今の住所に紐づけ直す
        DB::table('purchases')
            ->join('addresses', 'addresses.user_id', '=', 'purchases.user_id')
            ->update(['purchases.address_id' => DB::raw('addresses.id')]);

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['shipping_postal_code', 'shipping_address', 'shipping_building_name']);
        });
    }
}
