<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Menu;
use Carbon\Carbon;
use App\Models\Category;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class OrderItemFactory extends Factory
{

    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $category = Category::factory()->create();
        $menu = Menu::factory()->create([
            'category_id'=>$category->id
        ]);

        // $orderId = $this->faker()->numberBetween(1,50);
        $qty = $this->faker->numberBetween(1,5);
        $price = $menu->price;
        $subTotal = $qty * $price;

        return [
            'order_id'=>Order::factory(),
            'menu_id'=>$menu->id,
            'qty'=>$qty,
            'price'=>$price,
            'subtotal'=>$subTotal,
            'status'=>$this->faker->randomElement(['pending','canceled','ongoing','completed']),
            'menu_name'=>$menu->name,

        ];
    }
}