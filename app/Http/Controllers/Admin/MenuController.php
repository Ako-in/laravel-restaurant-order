<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Admin;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // DBファサードをインポート
// use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menus = Menu::all();
        $categories = Category::all(); // カテゴリも取得

        // dd(Auth::user());

        // メニューのページネーション
        $menus = Menu::with('category')
            ->withCount([
            'orderItems as sales_count' => function ($query) {
                $query->whereHas('order', function ($q) {
                    // orderItemsの親であるOrderのcreated_atをフィルタ
                    $q->where('created_at', '>=', now()->subDays(30));
                })
                ->select(DB::raw('sum(qty)')); // 数量の合計をselect
            }
        ])
        ->sortable()
        ->paginate(30); // ページネーションを追加

        return view('admin.menus.index', compact('menus','categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $this->authorize('create', Auth::guard('admin')->user());
        $admin = Auth::guard('admin')->user();
        $categories = Category::all();
        $menu = new Menu();//からのMenuインスタンスを作成
        return view('admin.menus.create',compact('categories','menu','admin'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->authorize('create', Auth::guard('admin')->user());
        $this->authorize('create', Menu::class);
        // $this->authorize('store', Auth::guard('admin')->user());
        // dd('store');
        // dd($request->all());
        // dd($request->file('image_file')->getClientOriginalName());


        $validatedData = $request->validate([
            'category_id' => 'required',
            'name' => 'required',
            'price' => 'required',
            'description' => 'nullable|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:active,inactive',
            'stock' => 'required|integer|min:0',
        ], [
            'category_id.required' => 'カテゴリは必須です。',
            'name.required' => '名前は必須です。',
            'price.required' => '価格は必須です。',
            'price.numeric' => '価格は数値でなければなりません。',
            'image_file.image' => 'ファイルは画像でなければなりません。',
            'image_file.mimes' => '画像は jpeg, png, jpg, gif, svg のいずれかの形式でなければなりません。',
            'image_file.max' => '画像サイズは 2MB を超えることはできません。',
            'stock.required' => '在庫は必須です。',
            'stock.integer' => '在庫は整数でなければなりません。',
            'stock.min' => '在庫は 0 以上でなければなりません。',
        ]);
        $menu = new Menu();        
        $menu->name = $validatedData['name'];
        $menu->category_id = $validatedData['category_id'];
        $menu->price = $validatedData['price'];
        $menu->stock = $validatedData['stock'];
        $menu->status = $validatedData['status'];
        // 画像アップロード
        $imagePath = null;

        // 画像アップロードの処理
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('images', 'public'); // 'menus'ではなく'images'に統一
            $menu->image_file = $imagePath; // ★ここを修正 (カラム名を 'image_file' に)

        } else {
            // 画像がアップロードされなかった場合のデフォルト設定
            $menu->image_file = 'storage/images/noimage.png'; // storage/images/noimage.png を指すように
        }
        
        $menu->save();

        return redirect()->route('admin.menus.index')->with('success','メニューを追加しました。');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function show(Menu $menu)
    {
        return view('admin.menus.show', compact('menu'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function edit(Menu $menu)
    {
        // 現在ログインしているAdminユーザーを取得
        $admin = Auth::guard('admin')->user();
        // dd(Auth::user());

        $categories = Category::all();
        return view('admin.menus.edit', compact('menu','categories','admin'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Menu $menu)
    {
        // $this->authorize('update', Auth::guard('admin')->user());
        $this->authorize('update', $menu);
        $validatedData=$request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive', // 'status' のバリデーションを追加/確認
            'stock' => 'required|integer|min:0', // `required` を追加
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 画像のバリデーション
        ],[
            'image_file.image' => '画像ファイルをアップロードしてください。',
            'image_file.mimes' => '画像は jpeg, png, jpg, gif, svg のいずれかの形式である必要があります。',
            'image_file.max' => '画像サイズは 2MB を超えることはできません。',
            'category_id.exists' => '選択されたカテゴリは存在しません。',
            'status.in' => 'ステータスは active または inactive のいずれかである必要があります。',
        ]);

        $menu->name = $validatedData['name'];
        $menu->price = $validatedData['price'];
        $menu->category_id = $validatedData['category_id'];
        $menu->description = $validatedData['description'];
        $menu->status = $validatedData['status'];
        $menu->stock = $request->input('stock',0);
        $menu->is_new = $request->input('is_new');
        $menu->is_recommended = $request->input('is_recommended');
        if ($request->hasFile('image_file')) {
            // 古い画像がある場合、`storage/`プレフィックスを考慮して削除
            if ($menu->image_file && Storage::disk('public')->exists($menu->image_file)) {
                Storage::disk('public')->delete($menu->image_file);
            }
            $path = $request->file('image_file')->store('images','public');

            $menu->image_file = $path; // カラム名を 'image_file' に
        } 

        $menu->save();

        return redirect()->route('admin.menus.index')->with('success', 'メニューが正常に更新されました。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();
        return to_route('admin.menus.index')->with('flash_message','menuを削除しました。');
    }

}
