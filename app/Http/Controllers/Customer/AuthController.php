<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        Log::info('ログ確認: ' . now());
        return view('customer.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'table_number' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('customer')->attempt(['table_number' => $credentials['table_number'], 'password' => $credentials['password']])) {
            return redirect()->route('customer.menus.index')->with('notice','お支払いはクレジットカードのみです。予めご了承ください。');
        }

        return redirect()->route('customer.login')->withErrors(['table_number' => 'ログイン情報が正しくありません']);
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        // セッションを無効化し、CSRFトークンを再生成
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('customer.login')->with('success', 'ログアウトしました。');
    }
}
