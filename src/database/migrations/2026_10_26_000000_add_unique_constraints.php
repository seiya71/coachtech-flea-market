<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1つの商品が購入されるのは1回だけ
        // (購入は支払いに関わるデータなので、重複があっても自動では削除せずに止める)
        $duplicatedItemIds = DB::table('purchases')
            ->select('item_id')
            ->groupBy('item_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('item_id');

        if ($duplicatedItemIds->isNotEmpty()) {
            throw new RuntimeException(
                '同じ商品の購入が複数あります。確認してから再度実行してください。item_id: ' . $duplicatedItemIds->implode(', ')
            );
        }

        Schema::table('purchases', function (Blueprint $table) {
            $table->unique('item_id');
        });

        // 住所は1人1件（重複していれば、最後に更新された1件を残す）
        $duplicatedUserIds = DB::table('addresses')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($duplicatedUserIds as $userId) {
            $keepId = DB::table('addresses')
                ->where('user_id', $userId)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->value('id');

            DB::table('addresses')->where('user_id', $userId)->where('id', '!=', $keepId)->delete();
        }

        Schema::table('addresses', function (Blueprint $table) {
            $table->unique('user_id');
        });

        // 同じ商品に同じカテゴリは1回だけ（重複していれば、最初の1件を残す）
        $keepIds = DB::table('item_category')
            ->selectRaw('MIN(id) as id')
            ->groupBy('item_id', 'category_id')
            ->pluck('id');

        DB::table('item_category')->whereNotIn('id', $keepIds)->delete();

        Schema::table('item_category', function (Blueprint $table) {
            $table->unique(['item_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // ユニークインデックスは外部キーのインデックスを兼ねているので、
        // 外部キーを外してから削除し、外部キーを作り直す（元のインデックスも作り直される）
        Schema::table('item_category', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropUnique(['item_id', 'category_id']);
            $table->foreign('item_id')->references('id')->on('items');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropUnique(['item_id']);
            $table->foreign('item_id')->references('id')->on('items');
        });
    }
}
