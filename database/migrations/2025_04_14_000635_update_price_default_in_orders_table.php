<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //priceカラム
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('price'); // まず削除
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price',8,2)->default(0); // デフォルト０で再作成
        });

        //qtyカラム
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('qty'); // まず削除
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('qty')->default(0); // デフォルト０で再作成
        });

        //subtotalカラム
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('subtotal'); // まず削除
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0); // デフォルト０で再作成
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //priceカラム
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('price');//削除
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price',8,2);//元に戻す、デフォルトなし
        });

        //qtyカラム
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('qty');//削除
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('qty');//元に戻す、デフォルトなし
        });

        //subtotalカラム
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('subtotal');//削除
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2);//元に戻す、デフォルトなし
        });
    }
};
