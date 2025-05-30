

@extends('layouts.admin')
    @section('content')
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-800">Add Menu</h1>
                <a href="{{ route('admin.menus.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Back</a>
            </div>
            <div class="mt-4">
                {{-- <form action="{{ route('admin.menus.store') }}" method="POST">
                    @csrf
                    <div class="flex flex-col mt-4">
                        <label for="name" class="text-gray-800">Name</label>
                        <input type="text" name="name" id="name" class="border border-gray-200 px-4 py-2 mt-2">
                    </div>
                    <div class="flex flex-col mt-4">
                        <label for="slug" class="text-gray-800">Category</label>
                        <select name="category_id" id="category" class="border border-gray-200 px-4 py-2 mt-2">
                          <option value="" disabled selected>-- Select a Category --</option>
                          @foreach ($categories as $category)
                              <option value="{{ $category->id }}">{{ $category->name }}</option>
                          @endforeach
                      </select>
                        {{-- <input type="text" name="category" id="category" class="border border-gray-200 px-4 py-2 mt-2"> --}}
                    {{-- </div>
                    <div class="flex flex-col mt-4">
                      <label for="slug" class="text-gray-800">Description</label>
                      <input type="text" name="description" id="description" class="border border-gray-200 px-4 py-2 mt-2">
                    </div>
                  
                    <div class="flex flex-col mt-4">
                        <label for="price" class="text-gray-800">Price</label>
                        <input type="text" name="price" id="price" class="border border-gray-200 px-4 py-2 mt-2">
                    </div>
                  
                    <div class="flex flex-col mt-4">
                        <label for="stock" class="text-gray-800">Stock</label>
                        <input type="text" name="stock" id="stock" class="border border-gray-200 px-4 py-2 mt-2">
                    </div>
                  
                    <div class="flex flex-col mt-4">
                        <label for="image" class="text-gray-800">Image</label>
                        <input type="text" name="image" id="image" class="border border-gray-200 px-4 py-2 mt-2">
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form> --}}
                <form action="{{ route('admin.menus.store') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="flex flex-col mt-4">
                      <label for="category_id" class="text-gray-800">Category</label>
                      <select name="category_id" id="category_id" class="border border-gray-200 px-4 py-2 mt-2">
                          <option value="">Select a category</option>
                          @foreach ($categories as $category)
                              {{-- <option value="{{ $category->id }}">{{ $category->name }}</option> --}}
                              <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                              </option>
                          @endforeach
                      </select>
                      @error('category_id')
                          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                      @enderror
                  </div>
                  <!-- 他のフィールド（name, price, descriptionなど） -->
                  <div class="flex flex-col mt-4">
                      <label for="name" class="text-gray-800">Name</label>
                      <input type="text" name="name" id="name" class="border border-gray-200 px-4 py-2 mt-2"value="{{ old('name') }}">
                      @error('name')
                          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                      @enderror
                  </div>
              
                  <div class="flex flex-col mt-4">
                      <label for="price" class="text-gray-800">Price</label>
                      <input type="number" name="price" id="price" class="border border-gray-200 px-4 py-2 mt-2" value="{{old('price')}}">
                      @error('price')
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
              
                  <div class="flex flex-col mt-4">
                      <label for="image" class="text-gray-800">Image</label>
                      <input type="file" name="image_file" id="image_file" class="border border-gray-200 px-4 py-2 mt-2">
                      @error('image_file')
                          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                      @enderror
                  </div>
              
                  <div class="flex flex-col mt-4">
                      <label for="status" class="text-gray-800">Status</label>
                      <select name="status" id="status" class="border border-gray-200 px-4 py-2 mt-2"value="{{old('status')}}">
                          <option value="active">Active</option>
                          <option value="inactive">Inactive</option>
                      </select>
                      @error('status')
                          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                      @enderror
                  </div>
              
                  <div class="flex flex-col mt-4">
                      <label for="stock" class="text-gray-800">Stock</label>
                      <input type="number" name="stock" id="stock" class="border border-gray-200 px-4 py-2 mt-2" min="0" value="{{old('stock')}}">
                      @error('stock')
                          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                      @enderror
                  </div>

                  <div class="flex flex-col mt-4">
                    <label for="is_new" class="text-gray-800">新商品フラグ</label>
                    {{-- <input type="radio" id="is_new" name="is_new" > --}}
                    <label><input type="radio" name="is_new" value="1" {{ old('is_new', optional($menu)->is_new) == 1 ? 'checked' : '' }}> あり</label>
                    <label><input type="radio" name="is_new" value="" {{ old('is_new', optional($menu)->is_new) == null ? 'checked' : '' }}> なし</label>


                  </div>

                  <div class="flex flex-col mt-4">
                    <label for="is_recommended" class="text-gray-800">おすすめフラグ</label>
                    {{-- <input type="radio" id="is_recommended" name="is_recommended" > --}}
                    <label><input type="radio" name="is_recommended" value="1" {{ old('is_recommended', optional($menu)->is_recommended) == 1 ? 'checked' : '' }}> あり</label>
                    <label><input type="radio" name="is_recommended" value="" {{ old('is_recommended', optional($menu)->is_recommended) == null ? 'checked' : '' }}> なし</label>
                    @error('status')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                  </div>
              
                  <button type="submit" class="bg-blue-500 text-black px-4 py-2 mt-4">Create Menu</button>
              </form>
              
            </div>
        </div>
    @endsection
