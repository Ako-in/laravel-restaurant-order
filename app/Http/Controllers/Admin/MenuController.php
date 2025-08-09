<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // DBファサードをインポート
// use Intervention\Image\Facades\Image;

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

        // 過去30日の売上個数を表示させたい
        // $menus = Menu::with('category') // カテゴリリレーションもEager Load
        // ->withCount([
        //     'orderItems as sales_count' => function ($query) {
        //         $query->whereHas('order', function ($q) {
        //             // orderItemsの親であるOrderのcreated_atをフィルタ
        //             $q->where('created_at', '>=', now()->subDays(30));
        //         })
        //         ->select(DB::raw('sum(qty)')); // 数量の合計をselect
        //     }
        // ])
        // ->sortable()
        // ->paginate(30);
        // // ->get();

        // $query = Menu::query();
        // //並べ替えを追加
        // // IDで並べ替え
        // $query->orderBy('id', 'desc');
        // // もしくは、created_atで並べ替え
        // $query->orderBy('created_at', 'desc');

        // //価格で並べ替え
        // $query->orderBy('price', 'asc'); 
        // // もしくは、価格で降順に並べ替え
        // $query->orderBy('price', 'desc');

        // // メニューのページネーション
        // $query->with('category'); // カテゴリもEager Load
        // // メニューのページネーション
        // $query->withCount('orderItems'); // 注文数をカウントするためのリレーションを追加
        // // メニューのページネーション
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

        // $menus = $query->paginate(30); 



        return view('admin.menus.index', compact('menus','categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $menu = new Menu();//からのMenuインスタンスを作成
        return view('admin.menus.create',compact('categories','menu'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
            'category_id.required' => 'Category is required.',
            'name.required' => 'Name is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            // 'image.required' => 'Image is required.',
            'image_file.image' => 'The file must be an image.',
            'image_file.mimes' => 'Image must be in jpeg, png, jpg, gif, or svg format.',
            'image_file.max' => 'Image size must not exceed 2MB.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be an integer.',
            'stock.min' => 'Stock must be 0 or more.',
        ]);
        $menu = new Menu();        
        $menu->name = $validatedData['name'];
        $menu->category_id = $validatedData['category_id'];
        $menu->price = $validatedData['price'];
        $menu->stock = $validatedData['stock'];
        $menu->status = $validatedData['status'];
        // 画像アップロード
        $imagePath = null;
        // if ($request->hasFile('image_file')) {
        //     $imagePath = $request->file('image_file')->store('images', 'public'); // 'public'ディスクに保存
        //     $menu->image_file = $imagePath; // カラム名を 'image_file' に
        // } else {
        //     // 画像がアップロードされなかった場合のデフォルト設定
        //     // 例えば、`noimage.png` をデフォルトにする場合
        //     $menu->image_file = 'storage/images/noimage.png'; // storage/images/noimage.png を指すように
        // }

        //     // 画像をストレージに保存（リサイズなし）
        //     // $image->storeAs('images', $filename, 'public'); // ★ここを修正

        //     $menu->image_file = $path; // カラム名を 'image_file' に

        //     $imagePath = $request->file('image_file')->store('images', 'public');
        //     // $menu->image_file = $imagePath;

        //     // 画像を読み込み、リサイズして保存
        //     // $img = Image::make($image)->fit(400, 400)->encode(); // 400x400ピクセルにリサイズし、アスペクト比を維持して切り抜き
        //     // Storage::disk('public')->put($imagePath, (string) $img); // 画像をストレージに保存
        //     $image->storeAs('images', $filename, 'public');
        //     $menu->image_file = $imagePath; // カラム名を 'image_file' に)
        
        // if ($request->hasFile('image_file')) {
        //     $image = $request->file('image_file');
        //     $imagePath = $image->store('images', 'public'); // publicディスクに保存
        // } else {
        // //     // return redirect()->back()->withErrors(['image' => 'Image file is required.']);
        //     $menu->image_file = 'storage/images/noimage.png';
        // }

    
        // 画像アップロードの処理
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('images', 'public'); // 'menus'ではなく'images'に統一
            $menu->image_file = $imagePath; // ★ここを修正 (カラム名を 'image_file' に)
        //     // $imagePath = $request->file('image_file')->store('images', 'public');
        //     // $menu->image_file = $imagePath; // ★ここを修正 (コメントアウトを解除)
        } else {
            // 画像がアップロードされなかった場合のデフォルト設定
            // 例えば、`noimage.png` をデフォルトにする場合
            $menu->image_file = 'storage/images/noimage.png'; // storage/images/noimage.png を指すように
        }
        // dd($imagePath);
        
        $menu->save(); // **データベースに保存**

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

        $categories = Category::all();
        return view('admin.menus.edit', compact('menu','categories'));
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
        $validatedData=$request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive', // 'status' のバリデーションを追加/確認
            'stock' => 'required|integer|min:0', // `required` を追加
            // 'is_new' => 'nullable|boolean',
            // 'is_recommended' => 'nullable|boolean',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 画像のバリデーション
        ],[
            'image_file.image' => 'The file must be an image...',
            'image_file.mimes' => 'Image must be in jpeg, png, jpg, gif, or svg format.',
            'image_file.max' => 'Image size must not exceed 2MB.',
            // 必要に応じて他のバリデーションメッセージも追加
            'category_id.exists' => 'Selected category does not exist.',
            'status.in' => 'Status must be active or inactive.',
        ]);
        // $imagePath = $menu->image_file; // 既存の画像パスを保持
        $menu->name = $validatedData['name'];
        $menu->price = $validatedData['price'];
        $menu->category_id = $validatedData['category_id'];
        $menu->description = $validatedData['description'];
        $menu->status = $validatedData['status'];
        // $menu->status = $request->input('status') === '1' ? 'active' : 'inactive';

        $menu->stock = $request->input('stock',0);
        $menu->is_new = $request->input('is_new');
        $menu->is_recommended = $request->input('is_recommended');
        if ($request->hasFile('image_file')) {
            // 古い画像がある場合、`storage/`プレフィックスを考慮して削除
            if ($menu->image_file && Storage::disk('public')->exists($menu->image_file)) {
                Storage::disk('public')->delete($menu->image_file);
            }
            $path = $request->file('image_file')->store('images','public');
            // $filename = time() . '.'. $image->getClientOriginalExtension();
            // $path = 'images/'. $filename;

            //画像とりこみ保存
            // $image->storeAs('images', $filename, 'public'); // 'menus'ではなく'images'に統一
            // $img = Image::make($image)->fit(400, 400)->encode(); // 400x400ピクセルにリサイズし、アスペクト比を維持して切り抜き
            // Storage::disk('public')->put($path, (string) $img); // 画像をストレージに保存
            $menu->image_file = $path; // カラム名を 'image_file' に
            // $path = $request->file('image_file')->store('menus', 'public'); // 'public'ディスクに保存
            // $menu->image = $path;
            // dd('画像をアップロードしました: ' . $path);
            // dd($menu->image_file);
        } 
        // else {
        //     $menu->image_file = '';
        // }
        $menu->save();

        // return redirect()->route('admin.menus.index')->with('flash_message', 'メニューを編集しました。');
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
