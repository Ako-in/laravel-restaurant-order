<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
// use App\Http\Controllers\Customer\MenuController;
use App\Http\Controllers\Admin;

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
// ====================管理者用=====================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('menus', \App\Http\Controllers\Admin\MenuController::class);
    
    Route::get('orders/print/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'print'])->name('orders.print');
    Route::get('orders/{id}/confirm',[\App\Http\Controllers\Admin\OrderController::class,'showConfirm'])->name('orders.showConfirm');
    Route::post('/orders/{id}/confirm', [\App\Http\Controllers\Admin\OrderController::class, 'storeConfirmedOrder'])->name('orders.storeConfirmed');
    // Route::post('orders/updateStatus/{id}',[\App\Http\Controllers\Admin\OrderController::class,'updateStatus'])->name('orders.updateStatus');
    Route::put('/orders/{id}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class);
});

Route::middleware('guest:admin')->group(function () {
    // Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('admin/login', [admin\Auth\AuthenticatedSessionController::class, 'create'])
                ->name('admin.login');

    Route::post('admin/login', [admin\Auth\AuthenticatedSessionController::class, 'store']);
    
   
});

Route::middleware('auth:admin')->group(function () {
    Route::post('admin/logout', [admin\Auth\AuthenticatedSessionController::class, 'destroy'])
                ->name('admin.logout');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'], function () {
    Route::get('home', [Admin\HomeController::class, 'index'])->name('home');
});

//===============ユーザー側================

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
    Route::get('/carts/history', [CartController::class, 'history'])->name('carts.history');
    Route::get('carts/checkout', [CartController::class, 'checkout'])->name('carts.checkout');
    Route::post('carts/checkoutStore', [CartController::class, 'checkoutStore'])->name('carts.checkoutStore');
    Route::get('carts/checkoutSuccess', [CartController::class, 'checkoutSuccess'])->name('carts.checkoutSuccess');
    // Route::get('checkouts',[CheckoutController::class, 'index'])->name('checkouts.index');
    // Route::post('checkouts',[CheckoutController::class, 'store'])->name('checkouts.store');
    // Route::get('checkouts/success',[CheckoutController::class, 'success'])->name('checkouts.success');
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


//テスト
Route::get('/test-confirm/{id}', function($id) {
    return 'ID: ' . $id;
});

