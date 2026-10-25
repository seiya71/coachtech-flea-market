<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // すでに二重に登録されているいいねは、最初の1件だけ残して削除する
        $keepIds = DB::table('likes')
            ->selectRaw('MIN(id) as id')
            ->groupBy('user_id', 'item_id')
            ->pluck('id');

        DB::table('likes')->whereNotIn('id', $keepIds)->delete();

        Schema::table('likes', function (Blueprint $table) {
            $table->unique(['user_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('likes', function (Blueprint $table) {
            // user_id の外部キーがこのインデックスを使うため、先に通常のインデックスを作っておく
            $table->index('user_id');
            $table->dropUnique(['user_id', 'item_id']);
        });
    }
}
