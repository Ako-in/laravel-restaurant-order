<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SalesTarget;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesTarget>
 */
class SalesTargetFactory extends Factory
{
    protected $model = SalesTarget::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        //デフォルトの期間をランダムで取得
        $periodType = $this->faker->randomElement(['monthly','yearly']);

        //ランダムな年と月を生成
        $year = $this->faker->numberBetween(Carbon::now()->year, Carbon::now()->year+5);
        $month = $this->faker->numberBetween(1,12);

        $starDate = Null;
        $endDate = Null;

        if ($periodType === 'monthly') {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        } elseif ($periodType === 'yearly') {
            // 例: 日本の会計年度（4月1日～翌年3月31日）
            $startDate = Carbon::create($year, 4, 1)->startOfDay();
            $endDate = Carbon::create($year + 1, 3, 31)->endOfDay();
        }

        return [
            'target_amount'=> $this->faker->numberBetween(10000,50000),
            'start_date'=> $startDate,
            'end_date'=> $endDate,
            'period_type'=>$periodType,

        ];
    }
}
