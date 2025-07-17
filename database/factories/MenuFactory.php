<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $foodNames = [
            'マルゲリータピザ', 'シーフードパスタ', 'シーザーサラダ', 'ブレンドコーヒー', '抹茶ティラミス',
            'ハンバーグ定食', 'オムライス', '特製カレーライス', 'フライドポテト', 'オレンジジュース',
            '季節のパンケーキ', 'ローストビーフ丼', '海老とアボカドのサンドイッチ', 'アイスコーヒー', 'ガトーショコラ',
            'チキン南蛮定食', '豚の生姜焼き定食', 'カツ丼', 'うどん', 'そば', 'コーラ', 'ウーロン茶'
        ];

        // $categoryIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        $imageFiles = [
            'images/noimage.png', // デフォルトの画像

        ];
        return [
            'name' => $this->faker->randomElement($foodNames), 
            'price' => $this->faker->numberBetween(300, 3000), // 300から3000の間のランダムな数値
            // 'category_id' => $this->faker->randomElement($categoryIds),
            'category_id' => Category::factory(),
            // 'description' => $this->faker->sentence(10), // 10単語のランダムな文章
            'status'=>$this->faker->boolean(80) ? 'active' : 'inactive',
            'stock'=>$this->faker->numberBetween(1,30),
            // 'is_recommended' => $this->faker->boolean(25) ? 1 : null, 
            // 'is_new' => $this->faker->boolean(20) ? 1 : null,
            'is_recommended' => $this->faker->boolean(25) ? 1 : 0, 
            'is_new' => $this->faker->boolean(20) ? 1 : 0,
            // 'image_file' => 'storage/images/noimage.png', //画像データがない場合はnoimage.pngが表示するようにBladeで設定済みのため一旦コメントアウト
            'image_file' => 'storage/' . $this->faker->randomElement($imageFiles),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'), // 過去1年間のランダムな日時
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }


}
