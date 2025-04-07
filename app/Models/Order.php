<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'menu_name',
        'price',
        'qty',
        'subtotal',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
