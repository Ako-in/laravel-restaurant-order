{{-- @extends('layouts.admin')
    @section('content')
        <div class="container">
            <div class="flex justify-between items-center">
                <h3 class="">カテゴリ新規作成</h3>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">戻る</a>
            </div>
            <div class="mt-4">
                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  {{-- <div class="flex flex-col mt-4">
                      <label for="category_id" class="text-gray-800">Category</label>

                      @error('category_id')
                          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                      @enderror
                  </div> --}}
{{-- 
                  <div class="flex mt-4 mb-4">
                      <label for="name" class="">カテゴリ名</label>
                      <input type="text" name="name" id="name" class=""value="{{ old('name') }}">
                      @error('name')
                          <p class="">{{ $message }}</p>
                      @enderror
                  </div>
              
                  <button type="submit" class="btn btn-primary">カテゴリ作成</button>
              </form>
              
            </div>
        </div>
    @endsection
 --}}

@extends('layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="mb-4">
            <h4>カテゴリ新規作成</h4>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">戻る</a>
        </div>

        <div class="card p-4">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">カテゴリ名</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">カテゴリ作成</button>
            </form>
        </div>
    </div>
@endsection
