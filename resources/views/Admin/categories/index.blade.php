@extends('layouts.admin')

@section('content')

{{-- <p>カテゴリ一覧</p> --}}

<div class="container">
  <div class="row">
    <div class="col-md-12">
        <h3>カテゴリ一覧</h3>
    </div>
    
</div>
<a href="{{route('admin.categories.create')}}" class="btn btn-primary px-4 py-2 mt-4 mb-4">カテゴリ作成</a>


<div class="d-flex flex-wrap">
    @foreach($categories as $category)
    <div class="card m-2" style="width: 18rem;">
        <div class="card-body">
            <h5 class="card-title">{{ $category->id }}</h5>
            <h5 class="card-title">{{ $category->name }}</h5>
            <p class="card-text">{{ $category->description }}</p>
        </div>
    </div>
    @endforeach
</div>  




    

@endsection

