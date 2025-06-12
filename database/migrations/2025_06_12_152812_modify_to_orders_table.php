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
            // =====削除するカラム======
            $table->dropColumn('menu_id');
            $table->dropColumn('menu_name');
            $table->dropColumn('qty');
            $table->dropColumn('price');
            $table->dropColumn('subtotal');

            // =====新規作成するカラム======
            $table->decimal('total_amount', 8, 2)->after('table_number')->default(0);
            
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
            //=====削除したカラムを戻す場合=======
            $table->unsignedBigInteger('menu_id')->after('table_number');
            $table->string('menu_name')->after('menu_id');
            $table->integer('qty')->after('menu_name');
            $table->decimal('price', 8, 2)->after('qty');
            $table->decimal('subtotal', 8, 2)->after('price');
            // =====新規作成したカラム======
            $table->dropColumn('total_amount');

        });
    }
};
