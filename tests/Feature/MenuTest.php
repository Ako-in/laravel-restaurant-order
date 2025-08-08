<?php

namespace Tests\Feature\admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Category;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class MenuTest extends TestCase
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

    // ============
    // admin側
    // ============
    public function test_admin_user_can_access_admin_menus_index()
    {
        //1.管理者としてログインしているユーザーが管理画面のメニューIndexにアクセスできる
        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin, 'admin'); //管理者としてでログイン
        $response = $this->get(route('admin.menus.index'));
        // $response->assertRedirect(); 
        $response->assertStatus(200);
    }

    public function test_admin_user_can_access_admin_menus_create()
    {
        //2.管理者としてログインしているユーザーが管理画面のメニューcreateにアクセスして保存ができる
        $category = Category::factory()->create();
        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin, 'admin'); //管理者としてでログイン
        $response = $this->get(route('admin.menus.create'));
        // $response = $this->actingAs($admin,'admin')->post(route('admin.menus.store'),[
        //     'name'=>'テスト',
        //     'price'=>'1200',
        //     'status'=>'active',
        //     'stock'=>'10',
        //     'is_recommended'=>null,
        //     'is_new'=>1,
        //     'image_file'=>'',
        //     'category_id'=>$category,
        // ]);
        // $menu = Menu::factory()->create();
        // $response->assertDatabaseHas(route('admin.menus.index'));
        $response = $this->get(route('admin.menus.create'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.menus.create'); // 正しいビューが表示されることを期待
        // $response->assertRedirect(route('admin.menus.index'));
        // $response->assertSessionHas('success');
    }

    public function test_admin_user_can_update_admin_menus()
    {
        //3.管理者としてログインしているユーザーは管理画面のメニューをUpdateできる
        $category = Category::factory()->create();
        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        // $menu = Menu::factory()->create();
        $menu = Menu::create([
            'name' => 'テスト',
            'price' => '300',
            'status' => 'inactive',
            'stock' => 1,
            'is_recommended' => 1,
            'is_new' => 1,
            'image_file' => '',
            'category_id' => $category->id,
            'description' => '',
        ]);
        $response = $this->actingAs($admin, 'admin')->put(route('admin.menus.update', $menu), [
            'name' => 'テストupdate',
            'price' => '300',
            'status' => 'active',
            'stock' => 10,
            'is_recommended' => 1,
            'is_new' => 1,
            'image_file' => '',
            'category_id' => $category->id,
            'description' => '',
        ]);
        // $response=$this->get(route('admin.menus.edit',$menu));
        $response->assertRedirect(route('admin.menus.index'));
        $response->assertSessionHas('success');
        // $response -> assertStatus(200);

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => 'テストupdate',
            'status' => 'active',
            'stock' => 10,
        ]);

        $this->assertDatabaseMissing('menus', [
            'id' => $menu->id,
            'name' => 'テスト',
            'status' => 'inactive',
            'stock' => 1,
        ]);
    }

    public function test_admin_user_can_access_admin_menus_edit()
    {
        //4.管理者としてログインしているユーザーが管理画面のメニューEditにアクセスできる
        $category = Category::factory()->create();
        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin, 'admin'); //管理者としてでログイン
        $menu = Menu::factory()->create([
            'category_id' => $category->id,
        ]);
        $response = $this->get(route('admin.menus.edit', $menu));
        // $response->assertRedirect(); 
        $response->assertStatus(200);
    }

    // ==============    
    //カスタマー側
    // ==============

    public function test_customer_user_cannot_access_to_admin_menus_index()
    {
        //1.カスタマーユーザーは管理画面メニューindexにアクセスできない。
        $category = category::factory()->create();
        $menu = Menu::factory()->create([
            'category_id' => $category->id,
        ]);
        $customer = customer::factory()->create([
            'table_number' => '1',
            'password' => Hash::make('test')
        ]);

        $this->actingAs($customer, 'customer');
        $response = $this->get(route('admin.menus.index'));
        // $response->assertStatus(403);
        $response->assertRedirect(route('admin.login'));
        $response->assertStatus(302);
    }

    public function test_customer_user_cannot_access_admin_menu_create()
    {
        //2.カスタマーユーザーは管理画面メニューcreateにアクセスできない。  
        $customer = customer::factory()->create([
            'table_number' => '1',
            'password' => Hash::make('test')
        ]);

        $this->actingAs($customer, 'customer');
        $response = $this->get(route('admin.menus.create'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_user_cannot_access_admin_menu_edit()
    {
        //3.カスタマーユーザーは管理画面メニューEditにアクセスできない。
        $category = Category::factory()->create();
        $customer = customer::factory()->create([
            'table_number' => '1',
            'password' => Hash::make('test')
        ]);

        $this->actingAs($customer, 'customer');
        $menu = Menu::factory()->create([
            'category_id' => $category->id,
        ]);
        $response = $this->get(route('admin.menus.edit', $menu));
        $response->assertRedirect(route('admin.login'));
    }
}
