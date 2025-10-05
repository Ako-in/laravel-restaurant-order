<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\Order;
use App\Models\Menu;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class AdminPolicy
{
    use HandlesAuthorization;

    public function before(Admin $admin){
        // Log::debug('AdminPolicy確認',[
        //     'admin_email' => $admin->email ?? 'メールアドレスなし',
        //     'policy_status' => $admin->email === 'guest@example.com' ? 'DENY' : 'PASS TO METHOD',
        // ]);
        if($admin->email === 'guest@example.com'){
            return Response::deny('デモアカウントではデータの変更ができません');
        }
        return null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Admin $admin)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Admin $admin)
    {
        // // ログインユーザーのemailが 'guest' であるかをチェック
        // if($admin->email === 'guest@example.com'){
        //     return Response::deny('デモアカウントではデータの変更ができません。');
        // }
        return true; // それ以外のユーザーは許可
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Admin $admin, Menu $menu)
    {
        // // ログインユーザーのemailが 'guest' であるかをチェック
        // if($admin->email === 'guest@example.com'){
        //     return Response::deny('デモアカウントではデータの変更ができません。');
        // }
        return true; // それ以外のユーザーは許可
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     * update, delete, view は、Admin モデルがリソースを操作するため、
     * 標準的な形式として第二引数 $model (操作対象) を追加
     */
    public function delete(Admin $admin, Menu $menu)
    {
        // // ログインユーザーのemailが 'guest' であるかをチェック
        // if($admin->email === 'guest@example.com'){
        //     return Response::deny('デモアカウントではデータの変更できません。');
        // }
        return true; // それ以外のユーザーは許可
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Admin $admin)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Admin $admin)
    {
        //
    }
}
