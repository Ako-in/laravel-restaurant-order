<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Customer;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthTest extends TestCase
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

    // ログインページが正しく表示される
    public function test_admin_login_screen_can_be_rendered(){
        $response = $this->get('admin/login');
        $response ->assertStatus(200);
    }
    // 正しいメールアドレスとパスワードを入力すればログインできる
    public function test_admin_user_AuthenticationAfterLogin(){
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);

        $response = $this->post('admin/login',[
            'email' => 'admin@example.com',
            'password' => 'restaurant',
        ]);
        $this->assertAuthenticated('admin');
        $this->assertAuthenticatedAs($admin,'admin');

        $response->assertRedirect('admin/home');

    }
    //不正なパスワードを入力した場合、ログインできない
    public function test_admin_users_can_not_authenticate_with_invalid_password(){
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $response = $this->post('admin/login',[
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);$this->assertGuest();
    }
    //ログイン中のAdminユーザーはログアウトできる
    public function test_admin_user_can_logout(){
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $response = $this->post('admin/login',[
            'email' => 'admin@example.com',
            'password' => 'restaurant',
        ]);
        $response = $this->actingAs($admin)->post('admin/logout');
        $this->assertGuest();

        $response->assertRedirect('admin/login');
        
    }

}