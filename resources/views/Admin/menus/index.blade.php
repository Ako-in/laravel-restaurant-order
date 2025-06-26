@extends('layouts.admin')

@section('content')
<div class="">
    <h4 class="mt-4">メニュー・在庫一覧</h4>
    <a href="{{ route('admin.menus.create') }}"class="btn btn-success"> ＋新規メニュー</a>
    <a href="{{ route('admin.categories.index')}}"class="btn btn-warning">カテゴリー表示</a>
</div>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>カテゴリ名</th>
            <th>名前</th>
            <th>説明</th>
            <th>価格</th>
            <th>Image</th>
            
            <th>在庫数</th>
            {{-- <th>発注状況</th>
            <th>入荷予定日</th>
            <th>入荷数量</th> --}}
            <th>販売ステータス</th>
            <th>30日間の売上個数</th>
            <th>新商品</th>
            <th>おすすめ</th>
            <th>登録日</th>
            <th>更新日</th>
            {{-- <th>Category Name</th> --}}
            {{-- <th >Action</th> --}}
        </tr>
    </thead>

    <div class="col-9">
        <div class="container mt-3">
            並び替え:
            @sortablelink('id', 'ID')
            @sortablelink('category.name', 'カテゴリ名')
            {{-- @sortablelink('name', 'Name') --}}
            @sortablelink('price', '価格')
            @sortablelink('stock', '在庫')
            @sortablelink('status', '販売ステータス')
            @sortablelink('sales_count', '30日間の売上個数')
            @sortablelink('is_new', '新商品')
            @sortablelink('is_recommended', 'おすすめ')
            @sortablelink('created_at', '作成日')
            @sortablelink('updated_at', '編集日')

        </div>
    </div>

   <tbody>
        @foreach ($menus as $menu)
        <tr>
            <td>{{ $menu->id }}</td>
            {{-- <td>{{ $category->name }}</td> --}}
            <td>{{ $menu->category->name ?? 'N/A' }}</td>
            <td>{{ $menu->name }}</td>
            <td>{{ $menu->description }}</td>
            <td>{{ $menu->price }}</td>
            <td>
                {{-- 画像表示部分 --}}
                <div class="text-center mb-4"> {{-- 画像を中央寄せし、下部にマージン --}}
                    @if ($menu->image_file) {{-- $menu->image_file が存在するかをチェック --}}
                        <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="img-fluid rounded" style="max-width: 100px; height: auto;">
                    @else
                        <img src="{{ asset('storage/images/noimage.png') }}" alt="No Image" class="img-fluid rounded" style="max-width: 100px; height: auto;">
                    @endif
                </div>
            </td>
            <td>{{ $menu->stock }}</td>
            {{-- <td>
                //済・未<br>
                <button type="submit" name="incoming_status" value="1" class="btn btn-info">詳細</button>
            </td>
            <td>//入荷日</td>
            <td>//入荷数量</td> --}}
            {{-- <td>{{ $menu->status ? 'Active' : 'Inactive' }}</td> --}}
            <td>{{ $menu->status === 'active' ? 'Active' : 'Inactive' }}</td>

            {{-- {{$menu->orders->where('created_at', '>=', now()->subMonth())->sum('order_items.qty')}} --}}
            <td>{{ $menu->sales_count ?? 0 }}</td>

            <td>{{ $menu->is_new ? '⚪︎' : '-' }}</td>
            <td>{{ $menu->is_recommended ? '⚪︎' : '' }}</td>
            
            <td>{{$menu->created_at}}</td>
            <td>{{$menu->updated_at}}</td>
            <td>
                <form action="{{ route('admin.menus.destroy',$menu->id) }}" method="POST">
                    <a href="{{ route('admin.menus.show',$menu->id) }}" class="btn btn-primary mb-2">確認(のちに必要性を判断)</a>
                    <a href="{{ route('admin.menus.edit',$menu->id) }}"class="btn btn-danger">編集</a>
                    {{-- @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button> --}}
                </form>
        </td>
        </tr>
        @endforeach
   </tbody>

</table>

<div class="d-flex justify-content-center">
    {{-- {{$menus->links()}} --}}
    {{$menus->appends(request()->query())->links()}}
  </div>

@endsection