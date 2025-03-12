<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Category;
use Illuminate\Http\Request;

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

        return view('admin.menus.index', compact('menus'));
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

        $request->validate([
            'category_id' => 'required',
            'name' => 'required',
            'price' => 'required',
            // 'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required',
            'stock' => 'required|integer|min:0',
        ], [
            'category_id.required' => 'Category is required.',
            'name.required' => 'Name is required.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'image.required' => 'Image is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be in jpeg, png, jpg, gif, or svg format.',
            'image.max' => 'Image size must not exceed 2MB.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be an integer.',
            'stock.min' => 'Stock must be 0 or more.',
        ]);

        // 画像アップロード
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image_file');
            $imagePath = $image->store('images', 'public'); // publicディスクに保存
        } else {
            return redirect()->back()->withErrors(['image' => 'Image file is required.']);
        }
        $menu = new Menu();
        $menu->name = $request->input('name');
        $menu->price = $request->input('price');
        $menu->category_id = $request->input('category_id');
        $menu->description = $request->input('description');
        // $menu->image = $request->file('image_file')->store('images');
        // $menu->status = $request->input('status');
        $menu->status = $request->input('status') === '1' ? 'active' : 'inactive';
        $menu->stock = $request->input('stock');
        $menu->is_new = $request->input('is_new');
        $menu->is_recommended = $request->input('is_recommended');
        $menu->save();
        // dd($menu);

        return to_route('admin.menus.index');
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

        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'price' => 'required|numeric|min:0',
        //     'category_id' => 'nullable|exists:categories,id',
        //     'description' => 'nullable|string',
        //     'status' => 'required|boolean',
        //     'stock' => 'required|integer|min:0', // `required` を追加
        //     // 'is_new' => 'nullable|boolean',
        //     // 'is_recommended' => 'nullable|boolean',
        // ]);
        $menu->name = $request->input('name');
        $menu->price = $request->input('price');
        $menu->category_id = $request->input('category_id');
        $menu->description = $request->input('description');
        // $menu->status = $request->input('status');
        $menu->status = $request->input('status') === '1' ? 'active' : 'inactive';

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
}
