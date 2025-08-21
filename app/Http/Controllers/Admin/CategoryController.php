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
        ];

        $validatedData = $request->validate($validationRules, $validationMessages);

        $category = Category::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'] ?? null,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'カテゴリは登録されました。');

    }
}
