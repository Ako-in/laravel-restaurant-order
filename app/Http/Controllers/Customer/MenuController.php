<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Customer;
use Carbon\Carbon;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $menus = Menu::all();
        // $menus = Menu::paginate(5);
        $categories = Category::all(); // カテゴリも取得
        $customer = Auth::user();

        // 注文可能時間を設定するための変数
        $now = Carbon::now();

        // 注文可能時間の設定（コントローラのみで変更可能）
        $startTimeCarbon = Carbon::createFromTime(9, 0, 0); // 9:00
        $closeTimeCarbon = Carbon::createFromTime(22, 0, 0); // 22:00
        $lastOrderTimeCarbon = Carbon::createFromTime(21, 30, 0); // ラストオーダー時間は21:30

        // Bladeで表示させるために時間文字列にする
        $startTime = $startTimeCarbon->format('H:i');
        $closeTime = $closeTimeCarbon->format('H:i');
        $lastOrderTime = $lastOrderTimeCarbon->format('H:i');

        // ラストオーダー前30分前にラストオーダー時間のアラートを出す
        $alertTime = $closeTimeCarbon->clone()->subMinutes(30);//21:30を取得

        if ($now->between($alertTime, $lastOrderTime)) { 
            session()->put('alert', 'ラストオーダーは21:30です。ご注意ください。');
        }else{
            session()->forget('alert');
        }
        // if ($now->between($closingTime->subMinutes(30), $closingTime)) {
        //     session()->flash('alert', 'ラストオーダー時間の30分前です。ご注意ください。');
        // }

        // 注文可能時間内かどうかをチェック
        $isOrderableTime = $now->between($startTime, $closeTime);

        $query = Menu::query();
        $totalCount = 0; // 初期値として0を設定
        // メニュー検索（名前検索）
        $search = $request->input('search');
        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
            // $totalCount = $query->count(); // 検索結果の総数を取得
        }

        // メニュー検索（キーワード、カテゴリ、価格帯）
        $categoryId = $request->input('category');
        $categoryName = Null;
        if ($categoryId) {
            // menusテーブルのcategory_idカラムがある場合
            $query->where('category_id', $categoryId);
            // $category = Category::find($categoryId);
            // if ($category) {
            //     $categoryName = $category->name;
            // }
            // $totalCount = $query->count(); // カテゴリで絞り込んだ後の総数を取得
        }

        $priceRange = $request->input('price_range');
        if ($priceRange) {
            // 価格帯のフォーマットは "min-max" であると仮定
            list($minPrice, $maxPrice) = explode('-', $priceRange);
            $query->whereBetween('price', [(int)$minPrice, (int)$maxPrice]);
            // $totalCount = $query->count(); // 価格帯で絞り込んだ後の総数を取得
        }

        // 新しい検索ボタンのパラメータ
        $recommend = $request->has('recommend');
        $newItem = $request->has('new_item');
        $hasStock = $request->has('has_stock');
        $stockLow = $request->has('stock_low');
        
        // おすすめ商品検索
        if ($request->has('recommend')) {
            $query->where('is_recommended', true);
        }

        // 新商品検索
        if ($request->has('new_item')) {
            $query->where('is_new', true);
        }

        // 在庫ありのみ
        if ($hasStock) {
            $query->where('stock', '>', 0); // 在庫数が0より大きいメニューを絞り込む
        }

        //残りわずか
        if ($stockLow) {
            $query->where('stock', '>', 0)->where('stock', '<', 5); // 在庫が1〜4のものを検索
        }

        // if($keyword !== null){
        //     $menus = Menu::where('name',function($query)use($keyword){
        //         $query->where('categories.name','like',"%{$keyword}%");
        //     })
        //     // ->orWhere('address', 'like', "%{$keyword}%")
        //     ->orWhere('name', 'like', "%{$keyword}%")

        //     ->orderBy('created_at','desc')
        //     ->paginate(4);

        //     $total = $menus->total();
        // }

        // $total = 0; // 初期値として0を設定
        // if($request !== null && $keyword !== null){
        //     $menus = Menu::where('name', 'like', "%{$keyword}%")
        //         ->orWhereHas('category', function($query) use ($keyword) {
        //             $query->where('name', 'like', "%{$keyword}%");
        //         })
        //         ->orderBy('created_at', 'desc')
        //         ->paginate(4);

        //     $total = $menus->total();
        // } else {
        //     $menus = Menu::orderBy('created_at', 'desc')->paginate(4);
        // }

        // メニュー検索（カテゴリ）


        // メニュー検索（価格帯）

        // 常に最新のものが上に来るように並べ替え
        $query->orderBy('created_at', 'desc');

        $totalCount = $query->count();

        // 全ての検索条件が適用されたクエリに対してページネーションを適用
        $menus = $query->paginate(8); // 例: 1ページあたり8件表示

        

        // $hasUnpaidOrder = false;
        // if (Auth::check()) {
        //     // ユーザーの未払いの注文を確認
        //     $hasUnpaidOrder = Order::where('table_number', Auth::id())
        //         ->where('is_paid', false)
        //         ->exists();
        // }
        return view('customer.menus.index',compact(
            'menus',
            'customer',
            'categories',
            'isOrderableTime',
            'search',
            'totalCount',
            'priceRange',
            'categoryId',
            'startTime',
            'closeTime',
            'lastOrderTime',
            'recommend',
            'newItem',
            'hasStock',
            'stockLow',
            // 'hasUnpaidOrder',
        ));
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
