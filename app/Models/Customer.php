<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Order;

class Customer extends Authenticatable
{
    //authenticatable Auth::guard('customer')でCustomerモデルを指定
    use HasFactory, Notifiable;

    protected $fillable = [
        'table_number',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function orders()
    {
        // hasMany
        return $this->hasMany(Order::class,'table_number');
    }

}
