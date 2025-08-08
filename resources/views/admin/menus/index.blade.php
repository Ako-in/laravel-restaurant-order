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
        <div class="container mt-3 d-flex flex-wrap gap-2">
            <span>並び替え:</span>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'id'] + request()->except('sort', 'page')) }}">ID</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'category.name'] + request()->except('sort', 'page')) }}">カテゴリ名</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'price'] + request()->except('sort', 'page')) }}">価格</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'stock'] + request()->except('sort', 'page')) }}">在庫</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'status'] + request()->except('sort', 'page')) }}">販売ステータス</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'sales_count'] + request()->except('sort', 'page')) }}">30日間の売上個数</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'is_new'] + request()->except('sort', 'page')) }}">新商品</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'is_recommended'] + request()->except('sort', 'page')) }}">おすすめ</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'created_at'] + request()->except('sort', 'page')) }}">作成日</a>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.menus.index', ['sort' => 'updated_at'] + request()->except('sort', 'page')) }}">編集日</a>
        </div>
    </div>

   <tbody>
        @foreach ($menus as $menu)
        <tr style="border-bottom: 1px solid black; text-align: center;">
            <td style="padding: 10px;">{{ $menu->id }}</td>
            {{-- <td>{{ $category->name }}</td> --}}
            <td style="padding: 10px;">{{ $menu->category->name ?? 'N/A' }}</td>
            <td style="padding: 10px;">{{ $menu->name }}</td>
            <td style="padding: 10px;">{{ $menu->description }}</td>
            <td style="padding: 10px;">{{ $menu->price }}</td>
            <td style="padding: 10px;">
                {{-- 画像表示部分 --}}
                <div class="text-center mb-4"> {{-- 画像を中央寄せし、下部にマージン --}}
                    @if ($menu->image_file) {{-- $menu->image_file が存在するかをチェック --}}
                        <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="img-fluid rounded" style="max-width: 100px; height: auto;">
                    @else
                        <img src="{{ asset('storage/images/noimage.png') }}" alt="No Image" class="img-fluid rounded" style="max-width: 100px; height: auto;">
                    @endif
                </div>
            </td>
            <td style="padding: 10px;">{{ $menu->stock }}</td>
            {{-- <td>
                //済・未<br>
                <button type="submit" name="incoming_status" value="1" class="btn btn-info">詳細</button>
            </td>
            <td>//入荷日</td>
            <td>//入荷数量</td> --}}
            {{-- <td>{{ $menu->status ? 'Active' : 'Inactive' }}</td> --}}
            <td style="padding: 10px;">{{ $menu->status === 'active' ? 'Active' : 'Inactive' }}</td>

            {{-- {{$menu->orders->where('created_at', '>=', now()->subMonth())->sum('order_items.qty')}} --}}
            <td style="padding: 10px;">{{ $menu->sales_count ?? 0 }}</td>

            <td style="padding: 10px;">{{ $menu->is_new ? '⚪︎' : '-' }}</td>
            <td style="padding: 10px;">{{ $menu->is_recommended ? '⚪︎' : '' }}</td>
            
            <td style="padding: 10px;">{{$menu->created_at}}</td>
            <td style="padding: 10px;">{{$menu->updated_at}}</td>
            <td style="padding: 10px;">
                <a href="{{route('admin.menus.edit',$menu->id)}}" class="btn btn-danger text-nowrap">編集</a>
                {{-- <form action="{{route('admin.menus.edit',$menu->id)}}" class="btn btn-danger">編集</form> --}}
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