<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Customer\AuthController;
// use App\Http\Controllers\Customer\MenuController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//管理者側
// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::resource('menus', MenuController::class);
// });
// 管理者用
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class);
});

//ユーザー側

Route::middleware('auth:customer')->group(function () {
    Route::get('/customer/menus', function () {
        return view('customer.menus.index');
    })->name('customer.menus.index');
    // Route::resource('menus', MenuController::class);
});

Route::prefix('customer')->name('customer.')->middleware('auth:customer')->group(function () {
    Route::resource('menus', \App\Http\Controllers\Customer\MenuController::class);
});
Route::prefix('customer')->name('customer.')->group(function () {
    //ユーザー側のログイン画面
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
// Route::prefix('customer')->name('customer.')->group(function () {
//     Route::resource('menus', MenuController::class);
// });

