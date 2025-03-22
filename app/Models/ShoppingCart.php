<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ShoppingCart extends Model
{
    use HasFactory;
    
    protected $table = 'shoppingcart';

    public static function getCurrentUserOrders($user_id)
    {
        // $shoppingcarts = DB::table('shoppingcart')->where("instance","{$user_id}")->get();

        // Eloquent を使用してデータ取得
        $shoppingcarts = self::where("instance", $user_id)->get();
        $orders = [];
        foreach($shoppingcarts as $order){
            $orders[]=[
                'id'=>$order->number,
                'total'=>$order->price_total,
                'table_number'=>$order->table_number,
                'code'=>$order->code,
                'created_at'=>$order->created_at,
            ];
        }
        return $orders;
    }

}
