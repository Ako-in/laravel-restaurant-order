<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name'=>'ソフトドリンク'],
            ['name'=>'アルコール'],
            ['name'=>'サラダ'],
            ['name'=>'魚料理'],
            ['name'=>'肉料理'],
            ['name'=>'ピザ'],
            ['name'=>'パスタ'],
            ['name'=>'デザート'],
            ['name'=>'その他'],
        ];
        DB::table('categories')->insert($categories);
    }
}
