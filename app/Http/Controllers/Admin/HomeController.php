<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index() {

        // 現在ログインしているAdminユーザーを取得
        $admin = Auth::guard('admin')->user();

        return view('admin.home',compact('admin'));
    }
}
