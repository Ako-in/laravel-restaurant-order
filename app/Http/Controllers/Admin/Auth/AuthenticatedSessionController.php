<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Admin\GuestLoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Admin;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::ADMIN_HOME);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function guestLogin()
{
    // ゲスト用のアカウント情報を取得
    $guestAdmin = Admin::where('email', 'guest@example.com')->first();

    if ($guestAdmin) {
        Auth::guard('admin')->login($guestAdmin);
        return redirect()->route('admin.home');
    }

    // ゲストアカウントが存在しない場合は、ログインページにリダイレクト
    return redirect()->route('admin.login')->withErrors('ゲストアカウントが見つかりません。');
}
}
