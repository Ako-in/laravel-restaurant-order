<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Order extends Model
{
    use HasFactory,Sortable;

    protected $table = 'orders';
    
    protected $fillable = [
        'table_number',
        // 'menu_name',
        // 'menu_id',
        // 'price',
        // 'qty',
        // 'subtotal',
        'status',
        'created_at',
        'total_amount',
    ];

    // ソート可能なカラムを定義
    public $sortable = [
        'created_at' => [ // created_at をキーとして定義
            'asc' => 'created_at asc',
            'desc' => 'created_at desc',
            'default_direction' => 'desc', // デフォルトで降順
        ],
        // 'created_at', // 日付
        // 'total_sales', // 売上金額
        // 'total_orders', // 売上個数
        // 'averageSales', // 売上平均
    ];

    // @sortablelink(total_orders)の呼び出しに使用
    public function totalOrdersSortable($query, $direction)
    {
        // 'total_orders' は集計結果のエイリアスなので、テーブル名を付けずにソート
        return $query->orderBy('total_orders', $direction);
    }

    // @sortablelink(total_sales)の呼び出しに使用
    public function totalSalesSortable($query, $direction)
    {
        // 'total_sales' は集計結果のエイリアスなので、テーブル名を付けずにソート
        return $query->orderBy('total_sales', $direction);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

}
