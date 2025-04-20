<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    
    protected $fillable = [
        'table_number',
        'menu_name',
        'menu_id',
        'price',
        'qty',
        'subtotal',
        'status',
        'created_at',
    ];

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

}
