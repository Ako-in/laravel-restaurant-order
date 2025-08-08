@extends('layouts.admin')

@section('content')
  <h3>売上アイテム別（過去30日）</h3>

  <a href="{{route('admin.sales.index')}}" class="text-decoration-none">戻る</a>

  <div>
    Sort By
    @sortablelink('id','ID')
    @sortablelink('price','Price')
    {{-- @sortablelink('menu_name','Menu Name') --}}
    @sortablelink('total_orders','売上個数')
    {{-- @sortablelink('total_sales','売上金額') --}}
    {{-- @sortablelink('stock','Stock') --}}
    {{-- @sortablelink('updated_at','Updated At') --}}
    {{-- @sortablelink('created_at','Created At') --}}
  </div>
     {{-- アイテムの並び替え --}}
    {{-- <form action="{{ route('admin.sales.salesItem') }}" method="GET">
      <select name="sort-item" id="sort" class="form-select" style="width: 200px; display: inline-block;">
        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>アイテム個数昇順</option>
        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>アイテム個数降順</option>
      </select>
      <button type="submit" class="btn btn-primary">並び替え</button>
    </form> --}}
  <table>
    <tr>
      <th>メニューID</th>
      <th>メニュー名</th>
      <th>単価</th>
      <th>x</th>
      <th>売上個数</th>
      <th>=</th>
      <th>売上金額</th>
      {{-- <th>在庫数</th> --}}
    </tr>
    @foreach ($salesItem as $item)
      <tr>
        <td>{{ $item->menu_id }}</td>
        <td>{{ $item->menu_name }}</td>
        <td>{{'@' . number_format($item->price, 0)}}</td>
        <td>x</td>
        <td>{{ $item->total_orders }}個</td>
        <td>=</td>
        <td>{{ number_format($item->total_sales) }}円</td>
        
        {{-- <td>{{ $item->stock }}</td> --}}
      </tr>
    @endforeach
  </table>

@endsection