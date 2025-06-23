<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Menu;
use App\Models\OrderItem;
use Kyslik\ColumnSortable\Sortable;

class OrderItem extends Model
{
    use HasFactory,Sortable;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id', 
        'menu_id', 
        'menu_name', 
        'price', 
        'qty',
        'subtotal',
        'status',
    ];

    public $sortable = [
        'menu_id',
        'menu_name',
        'price',
        // 'total_orders', // SUM(order_items.qty) のエイリアス
        // 'total_sales',  // SUM(order_items.price * order_items.qty) のエイリアス
    ];

    public function totalOrdersSortable($query, $direction)
    {
        // 'total_orders' は集計結果のエイリアスなので、テーブル名を付けずにソートします。
        return $query->orderBy('total_orders', $direction);
    }

    public function totalSalesSortable($query, $direction)
    {
        // 'total_sales' は集計結果のエイリアスなので、テーブル名を付けずにソートします。
        return $query->orderBy('total_sales', $direction);
    }


    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function menu(){
        return $this->belongsTo(Menu::class);
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

}
