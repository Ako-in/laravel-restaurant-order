<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // DBファサードをインポート

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
        $menus = Menu::with('category') // カテゴリリレーションもEager Load
                        ->withCount(['orderItems as sales_count' => function ($query) {
                            $query->whereHas('order', function ($q) { // orderItemsの親であるOrderのcreated_atをフィルタ
                                $q->where('created_at', '>=', now()->subDays(30));
                            })
                            ->select(DB::raw('sum(qty)')); // 数量の合計をselect
                        }])
                        ->get();



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
            // 'description' => 'required',
            // 'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required',
            'stock' => 'required|integer|min:0',
        ], [
            'category_id.required' => 'Category is required.',
            'name.required' => 'Name is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            // 'image.required' => 'Image is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be in jpeg, png, jpg, gif, or svg format.',
            'image.max' => 'Image size must not exceed 2MB.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be an integer.',
            'stock.min' => 'Stock must be 0 or more.',
        ]);

        // dd('store2');

        // 画像アップロード
        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('images', 'public');
            // $menu->image_file = $imagePath;
        
        // if ($request->hasFile('image')) {
        //     $image = $request->file('image');
        //     $imagePath = $image->store('images', 'public'); // publicディスクに保存
        } else {
            return redirect()->back()->withErrors(['image' => 'Image file is required.']);
        }
        // dd($imagePath);

        // **$menu を作成**
        $menu = new Menu();
        $menu->name = $validatedData['name'];
        $menu->category_id = $validatedData['category_id'];
        $menu->price = $validatedData['price'];
        $menu->stock = $validatedData['stock'];
        $menu->status = $validatedData['status'];
        $menu->image_file = $imagePath; // **ここでエラーが出ていた**

        // dd($imagePath);
        
        $menu->save(); // **データベースに保存**

        // Menu::create([
        //     'image' => $imagePath,
        //     'category_id' => $request->category_id,
        //     'name' => $request->name,
        //     'price' => $request->price,
        //     'description' => $request->description,
        //     'image' => $imagePath,
        //     'status' => $request->status,
        //     'stock' => $request->stock,
        //     'is_new' => $request->is_new,
        //     'is_recommended' => $request->is_recommended,
        // ]);
        // $menu = new Menu();
        // $menu->name = $request->input('name');
        // $menu->price = $request->input('price');
        // $menu->category_id = $request->input('category_id');
        // $menu->description = $request->input('description');
        // $menu->image = $request->file('image')->store('images','public');
        // // $menu->status = $request->input('status');
        // $menu->status = $request->input('status') === '1' ? 'active' : 'inactive';
        // $menu->stock = $request->input('stock');
        // $menu->is_new = $request->input('is_new');
        // $menu->is_recommended = $request->input('is_recommended');
        // $menu->save();
        // dd($menu);

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

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive', // 'status' のバリデーションを追加/確認
            'stock' => 'required|integer|min:0', // `required` を追加
            // 'is_new' => 'nullable|boolean',
            // 'is_recommended' => 'nullable|boolean',
        ]);
        $menu->name = $request->input('name');
        $menu->price = $request->input('price');
        $menu->category_id = $request->input('category_id');
        $menu->description = $request->input('description');
        $menu->status = $request->input('status');
        // $menu->status = $request->input('status') === '1' ? 'active' : 'inactive';

        $menu->stock = $request->input('stock',0);
        $menu->is_new = $request->input('is_new');
        $menu->is_recommended = $request->input('is_recommended');
        $menu->save();

        return redirect()->route('admin.menus.index')->with('flash_message', 'メニューを編集しました。');

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

    public function incoming()
    {
        
    }

}
