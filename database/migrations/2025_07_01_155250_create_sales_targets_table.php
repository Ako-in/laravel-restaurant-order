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
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->decimal('target_amount', 10, 2)->comment('売上目標金額');
            $table->date('start_date')->comment('開始日');
            $table->date('end_date')->comment('終了日')->nullable();
            $table->string('period_type')->comment('期間タイプ'); // 'daily', 'weekly', 'monthly','quarterly', 'yearly'
            $table->text('description')->nullable()->comment('目標に関する説明 (任意)'); // TEXT型 (NULLを許可)
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
        Schema::dropIfExists('sales_targets');
    }
};
