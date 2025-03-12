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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id');
            $table->text('description')->nullable();
            $table->integer('price')->unsigned();
            $table->string('image_file')->nullable();
            $table->string('status')->default('active');;
            $table->foreign('category_id')->references('id')->on('categories');
            $table->integer('stock')->default(0);
            $table->boolean('is_new')->nullable(); // 新商品フラグ,NullOk
            $table->boolean('is_recommended')->nullable(); // おすすめフラグ,NullOK

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
};
