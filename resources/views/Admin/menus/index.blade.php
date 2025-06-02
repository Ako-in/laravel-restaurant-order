@extends('layouts.admin')

@section('content')

<a href="{{ route('admin.menus.create') }}"class="btn btn-success"> ＋新規メニュー</a>
<a href="{{ route('admin.categories.index')}}"class="btn btn-warning">カテゴリー表示</a>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>メニューID</th>
            <th>カテゴリ名</th>
            <th>Name</th>
            {{-- <th>Description</th> --}}
            <th>Price</th>
            {{-- <th>Image</th> --}}
            
            <th>Stock</th>
            <th>Status</th>
            <th>30日間の売上個数</th>
            <th>新商品</th>
            <th>おすすめ</th>
            {{-- <th>Category Name</th> --}}
            {{-- <th >Action</th> --}}
        </tr>
    </thead>
   <tbody>
        @foreach ($menus as $menu)
        <tr>
            <td>{{ $menu->id }}</td>
            {{-- <td>{{ $category->name }}</td> --}}
            <td>{{ $menu->category->name ?? 'N/A' }}</td>
            <td>{{ $menu->name }}</td>
            {{-- <td>{{ $menu->description }}</td> --}}
            <td>{{ $menu->price }}</td>
            {{-- <th>{{$menu->image_file}}</th> --}}
            
            <td>{{ $menu->stock }}</td>
            {{-- <td>{{ $menu->status ? 'Active' : 'Inactive' }}</td> --}}
            <td>{{ $menu->status === 'active' ? 'Active' : 'Inactive' }}</td>


            {{-- {{$menu->orders->where('created_at', '>=', now()->subMonth())->sum('order_items.qty')}} --}}
            <td>{{ $menu->sales_count ?? 0 }}</td>

            <td>{{ $menu->is_new ? '⚪︎' : '-' }}</td>
            <td>{{ $menu->is_recommended ? '⚪︎' : '' }}</td>
            <td>
                    <form action="{{ route('admin.menus.destroy',$menu->id) }}" method="POST">
                        <a href="{{ route('admin.menus.show',$menu->id) }}" class="btn btn-primary">確認</a>
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

@endsection