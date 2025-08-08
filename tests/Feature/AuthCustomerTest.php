<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthcustomerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    // public function test_example()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    public function test_customer_user_can_login_to_customer_page()
    {
        //1.カスタマーユーザーはカスタマーログインができる
        $customer = customer::factory()->create([
            'table_number' => '101',
            'password' => Hash::make('101pass'),
        ]);

        $response = $this->post('customer/login', [
            'table_number' => '101',
            'password' => '101pass',
        ]);
        $this->actingAs($customer, 'customer');
        $this->assertAuthenticated('customer');
        $this->assertAuthenticatedAs($customer, 'customer');

        $response->assertRedirect('customer/menus');
    }

    //不正なパスワードを入力した場合、ログインできない
    public function test_customer_users_can_not_authenticate_with_invalid_password()
    {
        $customer = customer::factory()->create([
            'table_number' => '101',
            'password' => Hash::make('101pass'),
        ]);

        $response = $this->post('customer/login', [
            'table_number' => '101',
            'password' => '101pass',
        ]);
        $this->assertGuest();
    }
    //ログイン中の支払い済みのcustomerユーザーはログアウトできる
    public function test_paid_customer_user_can_logout()
    {
        $customer = customer::factory()->create([
            'table_number' => '101',
            'password' => Hash::make('101pass'),
        ]);

        $response = $this->post('customer/login', [
            'table_number' => '101',
            'password' => '101pass',
        ]);

        $order = Order::factory()->create([
            'table_number' => $customer->id,
            'status' => 'completed || canceled',
            'is_paid' => true, //支払い済み
        ]);

        $response = $this->actingAs($customer, 'customer')->post('customer/logout');
        $this->assertGuest('customer');

        $response->assertRedirect('customer/login');
        $response->assertSessionHas('success', 'ログアウトしました。');
    }

    //ログイン中の未払いのcustomerユーザーはログアウトできない
    public function test_unpaid_customer_user_cannot_logout()
    {
        $customer = customer::factory()->create([
            'table_number' => '101',
            'password' => Hash::make('101pass'),
        ]);

        $response = $this->post('customer/login', [
            'table_number' => '101',
            'password' => '101pass',
        ]);

        $order = Order::factory()->create([
            'table_number' => $customer->id,
            'status' => 'ongoing || pending',
            'is_paid' => false, //未払
        ]);

        $response = $this->actingAs($customer, 'customer')->post('customer/logout');
        $this->assertGuest('customer');

        // $response->assertRedirect('customer/login');
        // $response->assertSessionHas('success', 'ログアウトしました。'); 
    }

    public function test_admin_user_cannot_login_to_customer()
    {
        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);

        $response = $this->post('customer/login', [
            'table_number' => 'admin_table', // 顧客としては存在しない
            // 'email' => 'admin@example.com',
            'password' => 'restaurant',
        ]);
        $this->assertGuest('customer');
        // $this->assertAuthenticatedAs($admin,'admin');

        $response->assertRedirect('customer/login');
        $response->assertSessionHasErrors(); // 何らかのエラーがあることをアサート

    }
}
