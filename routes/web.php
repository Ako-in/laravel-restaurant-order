<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CartController;
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
    // Route::resource('customer/carts', CartController::class);
    Route::get('carts', [CartController::class, 'index'])->name('carts.index');
    // Route::get('carts/{cart}', [CartController::class, 'show'])->name('carts.show');
    Route::post('carts/store', [CartController::class, 'store'])->name('carts.store');
    // Route::delete('carts/{cart}', [CartController::class, 'destroy'])->name('carts.destroy');
    // Route::post('carts/{cart}', [CartController::class, 'update'])->name('carts.update');
    // Route::get('carts/{cart}/edit', [CartController::class, 'edit'])->name('carts.edit');
    // Route::get('carts/success', [CartController::class, 'success'])->name('carts.success');

    Route::post('/orders/store', [CartController::class, 'storeOrder'])->name('orders.store');
    Route::get('/orders/complete', function () {
        return view('customer.orders.complete');
    })->name('orders.complete');
    Route::get('/orders/history', [CartController::class, 'history'])->name('orders.history');
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

