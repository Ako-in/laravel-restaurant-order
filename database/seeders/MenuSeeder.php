<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 基本的なランダムメニューを3件生成
        Menu::factory()->count(3)->create();

        // 例: 在庫切れのメニューを5件生成
        // Menu::factory()->outOfStock()->count(1)->create();

        // 例: おすすめメニューを10件生成
        // Menu::factory()->popular()->count(2)->create();

        // 特定の属性を上書きして数件生成
        Menu::factory()->count(3)->create([
            'category_id' => 1, // 特定のカテゴリのメニュー
            'price' => 500,     // 低価格のメニュー
            'is_new' => 1,      // 新着メニュー
        ]);
    }
}
