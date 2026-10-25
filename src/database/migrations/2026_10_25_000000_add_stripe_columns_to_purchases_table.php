<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeColumnsToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // 同じ決済を二重に登録しないように、Stripe の決済IDを記録する
            $table->string('stripe_session_id')->nullable()->unique()->after('address_id');
            // pending: コンビニ払いの支払い待ち / paid: 支払い済み
            $table->string('status')->default('paid')->after('stripe_session_id');
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
            $table->dropUnique(['stripe_session_id']);
            $table->dropColumn(['stripe_session_id', 'status']);
        });
    }
}
