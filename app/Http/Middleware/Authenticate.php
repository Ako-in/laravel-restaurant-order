<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // 管理者用ログイン
        if ($request->is('admin/*')) {
            return route('admin.login');
        }
        // カスタマー用ログイン
        return $request->expectsJson() ? null : route('customer.login');
        // if (! $request->expectsJson()) {
        //     return route('login');
        // }
    }

    // protected function redirectTo(Request $request): ?string
    // {
    //     if ($request->is('admin/*')) {
    //         return route('admin.login');
    //     }

    //     return $request->expectsJson() ? null : route('login');
    // }
}
