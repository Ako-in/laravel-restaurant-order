<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;    
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Cart;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //layout.app
        View::composer('layouts.app',function($view){
            
            // dd('View Composer is running for app.blade.php!', Auth::check(), session('table_number')); 
            $hasUnpaidOrders = false;//default値を設定
            $tableNumbers = null; // 初期値を設定

            if(Auth::check()){
                $user = Auth::user();
                // $table_number = Auth::user()->table_number; // ユーザーのテーブル番号を取得
                // ユーザーの未払いの注文を確認
                // if(class_exists(\Cart::class)&& Cart::session($user->id)->getTotal() > 0){
                //     $hasUnpaidOrder = true; // 未払いの注文がある場合
           
                $currentTableNumber = session('table_number');
                $tableNumber = $currentTableNumber; // セッションからテーブル番号を取得

                if ($currentTableNumber) {
                    if (Order::where('table_number', $currentTableNumber) 
                                        ->where('is_paid', '0') // 未払いの注文を確認
                                        ->exists()) {
                        $hasUnpaidOrders = true;
                    }
                }
                // if ($user->orders()->where('status', 'pending')->exists()) {
                //     $hasUnpaidOrders = true;
                // }
                // $tableNumber = $user->table_number; // ユーザーのテーブル番号を取得
            }
        
            // dd('View Composer finished. hasUnpaidOrders:', $hasUnpaidOrders);
            $view->with('hasUnpaidOrders', $hasUnpaidOrders);
            $view->with('tableNumber', $tableNumber);
            // dd('View Composer finished. hasUnpaidOrders:', $hasUnpaidOrders);
        });
    }
}
