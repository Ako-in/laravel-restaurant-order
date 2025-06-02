<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Customer;
use Carbon\Carbon;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $menus = Menu::all();
        $menus = Menu::paginate(5);
        $customer = Auth::user();

        // 注文可能時間を設定するための変数
        $now = Carbon::now();

        // 注文可能時間の設定（11:00から20:00まで）
        $startTime = Carbon::createFromTime(11, 0, 0); // 11:00
        $closingTime = Carbon::createFromTime(20, 0, 0); // ラストオーダー20:00

        // ラストオーダー前30分前にラストオーダー時間のアラートを出す
        if ($now->between($closingTime->subMinutes(30), $closingTime)) {
            session()->flash('alert', 'ラストオーダー時間の30分前です。ご注意ください。');
        }

        // 注文可能時間内かどうかをチェック
        $isOrderableTime = $now->between($startTime, $closingTime);
        return view('customer.menus.index',compact('menus','customer','isOrderableTime'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
