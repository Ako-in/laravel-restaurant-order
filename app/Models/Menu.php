<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Menu extends Model
{
    use HasFactory, Sortable;
    protected $fillable = [
        'name',
        'price',
        'category_id',
        'description',
        'status',
        'stock',
        // 'monthly_sales_count',
        'is_recommended',
        'is_new',
        'image_file',
        'updated_at',
    ];

    public $sortable = [
        'id',
        'name',
        'price',
        'category_id',
        'status',
        'stock',
        // 'monthly_sales_count',
        'is_recommended',
        'is_new',
        'updated_at',
    ];

    // メソッド名は `[カラム名]Sortable` の形式にする
    public function sales_countSortable($query, $direction)
    {
        // withCount で作成されたエイリアス名 'sales_count' を使用してソート
        return $query->orderBy('monthly_sales_count', $direction);
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
