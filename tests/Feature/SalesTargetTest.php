<?php

namespace Tests\Feature\test\admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Category;
use App\Models\SalesTarget;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SalesTargetTest extends TestCase
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
    public function test_admin_user_can_access_admin_salesTarget_index(){
        // 1.adminユーザーは売上目標Indexにアクセスできる
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin,'admin');//管理者としてでログイン
        $response = $this->get(route('admin.sales_target.index'));
        $response->assertStatus(200);
    }

    public function test_admin_user_can_access_admin_salesTarget_create(){
        // 2.adminユーザーは売上目標createにアクセスできる
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin,'admin');//管理者としてでログイン
        $response = $this->get(route('admin.sales_target.create'));
        $response->assertStatus(200);
    }

    public function test_admin_user_can_access_admin_salesTarget_edit(){
        // 3.adminユーザーは売上目標editにアクセスできる
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin,'admin');//管理者としてでログイン
        $salesTarget = SalesTarget::factory()->create();
        $response = $this->get(route('admin.sales_target.edit',$salesTarget));
        $response->assertStatus(200);    
    }






    // ==============    
    //カスタマー側
    // ==============
    public function test_customer_user_cannot_access_salesTarget_index(){
        // 1.カスタマーユーザーは売上目標Indexにアクセスできない
        $customer = Customer::factory()->create([
            'table_number'=>'1',
            'password'=>Hash::make('test')
        ]);

        $this->actingAs($customer,'customer');
        $response = $this->get(route('admin.sales_target.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_user_cannot_access_salesTarget_create(){
        // 2.カスタマーユーザーは売上目標createにアクセスできない  
        $customer = Customer::factory()->create([
            'table_number'=>'1',
            'password'=>Hash::make('test')
        ]);

        $this->actingAs($customer,'customer');
        $response = $this->get(route('admin.sales_target.create'));
        $response->assertRedirect(route('admin.login'));     
    }

    public function test_customer_user_cannot_access_salesTarget_edit(){
        // 3.カスタマーユーザーは売上目標editにアクセスできない
        $salesTarget = SalesTarget::factory()->create();
        $customer = Customer::factory()->create([
            'table_number'=>'1',
            'password'=>Hash::make('test')
        ]);

        $this->actingAs($customer,'customer');
        $response = $this->get(route('admin.sales_target.edit',$salesTarget));
        $response->assertRedirect(route('admin.login'));  

    }
}