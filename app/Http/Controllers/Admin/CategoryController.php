<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = new Category(); // Create a new instance of Category

        
        return view('admin.categories.create', compact('category'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Auth::guard('admin')->user());
        $validationRules = [
            'name' => 'required|string|max:50|unique:categories,name',
            // 'description' => 'nullable|string|max:50',
        ];

        $validationMessages = [
            'name.required' => 'カテゴリ名は必須です',
            'name.string' => 'カテゴリ登録は文字列のみです',
            'name.max' => '50文字以内で入力してください',
            'name.unique' => 'このカテゴリ名はすでに登録されています。',
            // 'description.string' => 'カテゴリ説明は文字列のみです',
            // 'description.max' => '50文字以内で入力してください',    
        ];

        $validatedData = $request->validate($validationRules, $validationMessages);

        $category = Category::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'カテゴリは登録されました。');

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
