<?php

namespace Tests\Feature\test\admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\admin;
use App\Models\customer;
use App\Models\Menu;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SalesTarget;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OrderTest extends TestCase
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
    public function test_admin_user_can_access_admin_orders_index()
    {
        // 1.adminユーザーはorders.Indexにアクセスできる
        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin, 'admin'); //管理者としてでログイン
        $response = $this->get(route('admin.orders.index'));
        $response->assertStatus(200);
    }

    public function test_admin_user_can_update_order_item_status()
    {
        // 2.adminユーザーはordersの個別ステータスを更新できる
        // $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'status' => 'pending', // 初期ステータスを明示的に設定
        ]);
        // OrderItemモデルにorder()リレーション
        $order = $orderItem->order;

        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin, 'admin'); //管理者としてでログイン
        // $response = $this->put(route('admin.orders.items.status',$order));
        $response = $this->put(route('admin.orders.updateOrderItemStatus', ['item' => $orderItem->id]), [
            'status' => 'completed', // 更新したい新しいステータス
        ]);

        $response->assertRedirect(route('admin.orders.showConfirm', ['id' => $order->id]));
        $response->assertSessionHas('success', '個別ステータス更新しました');

        // 変更後のデータになっているか確認
        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => 'completed',
        ]);

        //変更前のデータが残っていないか確認
        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
            'status' => 'pending',
        ]);

        // $response->assertStatus(200);
    }

    public function test_admin_user_can_access_admin_orders_updateQty()
    {
        // 3.adminユーザーはordersの数量を変更できる
        $orderItem = OrderItem::factory()->create([
            'qty' => 2,
        ]);
        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin, 'admin'); //管理者としてでログイン

        $response = $this->put(route('admin.orders.updateQty', ['item' => $orderItem->id]), [
            'qty' => 1, // 更新したい新しい数量
        ]);

        // $response->assertRedirect(route('admin.orders.storeConfirmed', ['id' => $orderItem->id]));
        $response->assertSessionHas('success', '数量を更新しました。');

        // 変更後のデータになっているか確認
        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'qty' => 1,
        ]);

        //変更前のデータが残っていないか確認
        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
            'qty' => 2,
        ]);
        // $response->assertStatus(200);    
    }

    public function test_admin_user_can_access_admin_orders_showConfirm()
    {
        // 3.adminユーザーはordersの詳細画面を表示できる
        $orderItem = OrderItem::factory()->create();
        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin, 'admin'); //管理者としてでログイン

        $response = $this->get(route('admin.orders.showConfirm', ['id' => $orderItem->id]));
        $response->assertStatus(200);
    }

    public function test_admin_user_can_access_admin_orders_updateAllStatus()
    {
        // 3.adminユーザーはordersの全体のステータスを更新できる
        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'status' => 'completed',
            'subtotal' => 1000,
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'status' => 'completed',
            'subtotal' => 2000,
        ]);


        $admin = admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('restaurant'),
        ]);
        $this->actingAs($admin, 'admin'); //管理者としてでログイン

        // $response = $this->put(route('admin.orders.updateAllStatus',['id'=>$orderItem->id]));
        // $response->assertStatus(200);



        // $response = $this->put(route('admin.orders.updateAllStatus', ['id' => $order->id]), [
        //     'status'=>'completed', // 更新したい新しいステータス
        // ]);

        // $response->assertRedirect(route('admin.orders.storeConfirmed', ['id' => $order->id]));


        $response = $this->put(route('admin.orders.updateAllStatus', ['order' => $order->id]));
        $response->assertRedirect(route('admin.orders.storeConfirmed', ['id' => $order->id]));

        // return redirect()->route('admin.orders.storeConfirmed', ['id' => $order->id]);

        // $response->assertSessionHas('success','を更新しました。');

        // 変更後のデータになっているか確認
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
            'total_amount' => 3000,
        ]);

        //変更前のデータが残っていないか確認
        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }







    // ==============    
    //カスタマー側
    // ==============
    public function test_customer_user_cannot_access_orders_index()
    {
        // 1.カスタマーユーザーはorders.Indexにアクセスできない
        $customer = customer::factory()->create([
            'table_number' => '1',
            'password' => Hash::make('test')
        ]);

        $this->actingAs($customer, 'customer');
        $response = $this->get(route('admin.orders.index'));
        $response->assertRedirect(route('admin.login'));
    }

    // public function test_customer_user_cannot_access_orders_create(){
    //     // 2.カスタマーユーザーはorders.createにアクセスできない  
    //     $customer = customer::factory()->create([
    //         'table_number'=>'1',
    //         'password'=>Hash::make('test')
    //     ]);

    //     $this->actingAs($customer,'customer');
    //     $response = $this->get(route('admin.orders.create'));
    //     $response->assertRedirect(route('admin.login'));     
    // }

    public function test_customer_user_cannot_access_orders_edit()
    {
        // 3.カスタマーユーザーはorders.editにアクセスできない
        $order = Order::factory()->create();
        $customer = customer::factory()->create([
            'table_number' => '1',
            'password' => Hash::make('test')
        ]);

        $this->actingAs($customer, 'customer');
        $response = $this->get(route('admin.orders.edit', $order->id));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_customer_user_cannot_access_order_updateQty()
    {
        //カスタマーユーザーは管理画面の注文数量をUpdateできない
        $order = Order::factory()->create();
        $customer = customer::factory()->create([
            'table_number' => '1',
            'password' => Hash::make('test')
        ]);
        $this->actingAs($customer, 'customer');
        $response = $this->get(route('admin.orders.showConfirm', ['id' => $order->id]));
        $response = $this->put(route('admin.orders.updateQty', $order->id));

        $response->assertRedirect('admin/login');
    }



    public function test_customer_user_cannot_access_order_showConfirm()
    {
        //カスタマーユーザーは管理画面の注文詳細確認画面にアクセスできない
        $order = Order::factory()->create();
        $customer = customer::factory()->create([
            'table_number' => '1',
            'password' => Hash::make('test')
        ]);
        $this->actingAs($customer, 'customer');
        $response = $this->get(route('admin.orders.showConfirm', ['id' => $order->id]));
        $response->assertRedirect('admin/login');
    }

    public function test_customer_user_cannot_update_order_item_status()
    {
        //カスタマーユーザーは管理画面の注文の個別ステータスをupdateできない
        $orderItem = OrderItem::factory()->create([
            'status' => 'pending',
        ]);

        $order = $orderItem->order;
        $customer = customer::factory()->create([
            'table_number' => '1',
            'password' => Hash::make('test')
        ]);
        $this->actingAs($customer, 'customer');
        // $response = $this->get(route('admin.orders.showConfirm',['id' => $order->id]));

        $response = $this->put(route('admin.orders.updateAllStatus', ['order' => $order->id]), [
            'status' => 'completed', // 更新したい新しいステータス
        ]);
        $response->assertRedirect(route('admin.login'));
        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => 'pending',
        ]);
    }

    public function test_customer_cannot_update_all_status()
    {
        //カスタマーユーザーは管理画面の注文ステータス全体をupdateできない
        $customer = customer::factory()->create();
        $orderItem = OrderItem::factory()->create([
            'status' => 'pending',
        ]);
        $order = $orderItem->order;
        $this->actingAs($customer, 'customer'); //カスタマーとしてでログイン

        // $response = $this->put(route('admin.orders.updateAllStatus',['id'=>$orderItem->id]));


        $response = $this->put(route('admin.orders.updateAllStatus', ['order' => $orderItem->id]), [
            'status' => 'completed', // 更新したい新しいステータス
        ]);
        $response->assertRedirect(route('admin.login'));

        $this->assertDatabaseMissing('order_items', [
            'id' => $orderItem->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'status' => 'pending',
        ]);
    }
}
