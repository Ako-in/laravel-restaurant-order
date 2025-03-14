<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('customer.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'table_number' => 'required',
            'password' => 'required',
        ]);

        // デバッグ: 認証情報を表示
    // dd($credentials, Auth::guard('customer')->attempt($credentials));


        if (Auth::guard('customer')->attempt(['table_number' => $credentials['table_number'], 'password' => $credentials['password']])) {
            return redirect()->route('customer.menus.index');
        }
        // if (Auth::guard('customer')->attempt($credentials)) {
        //     return redirect()->route('customer.dashboard');
        // }

        return back()->withErrors(['table_number' => 'ログイン情報が正しくありません']);
    }

    public function logout()
    {
        Auth::guard('customer')->logout();
        return redirect()->route('customer.login');
    }
}
