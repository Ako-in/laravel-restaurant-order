

@extends('layouts.admin')
    @section('content')
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-semibold text-gray-800">カテゴリ新規作成</h3>
                <a href="{{ route('admin.categories.index') }}" class="bg-blue-500 hover:bg-blue-600 text-black px-4 py-2 rounded">Back</a>
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

                  <div class="flex flex-col mt-4">
                      <label for="name" class="text-gray-800">Name</label>
                      <input type="text" name="name" id="name" class="border border-gray-200 px-4 py-2 mt-2"value="{{ old('name') }}">
                      @error('name')
                          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                      @enderror
                  </div>
              
                  <div class="flex flex-col mt-4">
                      <label for="description" class="text-gray-800">Description</label>
                      <textarea name="description" id="description" rows="4" class="border border-gray-200 px-4 py-2 mt-2"{{ old('description') }}></textarea>
                      @error('description')
                          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                      @enderror
                  </div>
              
                  <button type="submit" class="bg-blue-500 text-black px-4 py-2 mt-4">カテゴリ作成</button>
              </form>
              
            </div>
        </div>
    @endsection
