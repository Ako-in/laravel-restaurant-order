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
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('menu_name'); // カラム削除
        });
    
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('menu_name')->nullable(); // nullableで再作成
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('menu_name');
        });
    
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('menu_name'); // nullableではない元の状態に戻す
        });
    }
};
