@extends('layouts.admin')

@section('content')

<h3>売上メニュー</h3>

<div class="container">
  <a href="{{route('admin.sales.salesItem')}}">売上アイテム</a>
  <a href="{{route('admin.sales.salesAmount')}}">売上金額</a>
  <p>本日の売り上げ：{{$todaySalesFormatted}}円</p>
  <p>今月の売上累計：{{$monthlySalesFormatted}}円</p>
  <a href="{{route('admin.sales.chart')}}"class="btn btn-primary">売上分析</a>
  
</div>

<div class="d-flex justify-content-between align-items-end flex-wrap">
  {{-- <form method="GET" action="{{ route('admin.sales.index') }}" class="admin-search-box mb-3">
      <div class="input-group">
          <input type="text" class="form-control" placeholder="日付から検索" name="date" value="{{ $orderDate }}">
          <button type="submit" class="btn text-black shadow-sm">検索</button>
      </div>
  </form> --}}
</div>


