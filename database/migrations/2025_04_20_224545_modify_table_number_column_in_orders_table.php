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
        Schema::table('orders', function (Blueprint $table) {
            // カラムを一旦削除
            $table->dropColumn('table_number');
        });

        Schema::table('orders', function (Blueprint $table) {
            // カラムを再作成（nullable付き）
            $table->string('table_number')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // 再度削除して、nullableじゃない状態で元に戻す
            $table->dropColumn('table_number');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('table_number')->after('id'); // nullableなし
        });
    }
};
