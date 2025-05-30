@extends('layouts.admin')
@section('content')

  <div>
    <h2>Edit Menu</h2>
  </div>
  <div>
    <a href="{{ route('admin.menus.index') }}"> Back</a>
  </div>

  <form action="{{ route('admin.menus.update',$menu->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div>
        <strong>Name:</strong>
        <input type="text" name="name" value="{{ $menu->name }}" placeholder="Name">
    </div>
    <div>
        <strong>Description:</strong>
        <textarea style="height:150px" name="description" placeholder="description">{{ $menu->description }}</textarea>
    </div>
    <div>
        <strong>Price:</strong>
        <input type="number" name="price" value="{{ $menu->price }}" placeholder="{{$menu->price}}">
    </div>

    <div>
      <strong>Stock:</strong>
      <input type="number" name="stock"  value="{{ $menu->stock }}"placeholder="{{$menu->stock}}">
    </div>

    <div class="flex flex-col mt-4">
      <label for="category_id" class="text-gray-800">Category</label>
      <select name="category_id" id="category_id" class="border border-gray-200 px-4 py-2 mt-2">
          <option value="">Select a category</option>
          @foreach ($categories as $category)
              <option value="{{ $category->id }}" 
                  {{ old('category_id', $menu->category_id ?? '') == $category->id ? 'selected' : '' }}>
                  {{ $category->name }}
              </option>
          @endforeach
      </select>
      @error('category_id')
          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
      @enderror
  </div>
    {{-- <div>
      <strong>CategoryName:</strong>
      {{$menu->category_id}}
    </div> --}}
    


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
      <label for="is_new" class="text-gray-800">新商品フラグ</label>
      {{-- <input type="radio" id="is_new" name="is_new" > --}}
      <label><input type="radio" name="is_new" value="1" {{ old('is_new', $menu->is_new) == 1 ? 'checked' : '' }}> あり</label>
      <label><input type="radio" name="is_new" value="" {{ old('is_new', $menu->is_new) == null ? 'checked' : '' }}> なし</label>
      
      @error('status')
          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
      @enderror
    </div>

    <div class="flex flex-col mt-4">
      <label for="is_recommended" class="text-gray-800">おすすめフラグ</label>
      {{-- <input type="radio" id="is_recommended" name="is_recommended" > --}}
      <label><input type="radio" name="is_recommended" value="1" {{ old('is_recommended', $menu->is_recommended) == 1 ? 'checked' : '' }}> あり</label>
      <label><input type="radio" name="is_recommended" value="" {{ old('is_recommended', $menu->is_recommended) == null ? 'checked' : '' }}> なし</label>
      @error('status')
          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
      @enderror
    </div>
    
    {{-- <div>
      <strong>新商品フラグ:</strong>
      {{$menu->is_new ? 'あり' : 'なし'}} 
    </div>
    
    <div>
      <strong>おすすめフラグ:</strong>
      {{$menu->is_recommended ? 'あり' : 'なし'}} 
    </div> --}}
    
    <div class="mb-2">
      @if ($menu->image_file !== '')
          <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="w-100">
      @else
          <img src="{{ asset('/images/no_image.jpg') }}" class="w-100">
      @endif
    </div>
    




    <div>
        <button type="submit">Submit</button>
    </div>

  </form>

@endsection