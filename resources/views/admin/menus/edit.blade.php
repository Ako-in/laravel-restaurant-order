@extends('layouts.admin')
@section('content')
<div>
    @if($admin->email === 'guest@example.com')
        <div class="alert alert-warning text-center rounded-0 mb-0 py-2 pt-3" role="alert">
        <strong>💡 このアカウントはデモ用です。</strong> データの変更などはできません。
        </div>
    @endif
</div>

    <div class="container py-4">
        <div>
            <div class="d-flex justify-content-center mb-2"> {{-- タイトルを中央寄せ --}}
                <h4 class="mb-0">メニュー編集</h4>
            </div>
            <div class="mb-4"> {{-- 戻るボタンの親divに下マージン --}}
                <a href="{{ route('admin.menus.index') }}" class="btn btn-primary">戻る</a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="{{ route('admin.menus.update', $menu->id) }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-col mt-4">
                        <label for="category_id" class="text-gray-800">カテゴリ：</label>
                        <select name="category_id" id="category_id" class="form-select mb-3">
                            <option value="">カテゴリを選択</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $menu->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- 他のフィールド（name, price, descriptionなど） -->
                    <div class="mb-3">
                        <label for="name" class="form-label">名前</label>
                        <input type="text" name="name" value="{{ $menu->name }}" placeholder="Name"
                            class="form-select mb-3">
                        {{-- <input type="text" name="name" id="name" class="form-select"value="{{ old('name') }}"> --}}
                        @error('name')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">価格(税抜)</label>
                        <input type="number" name="price" id="price" class="form-select"
                            value="{{ $menu->price }}"placeholder="{{ $menu->price }}">
                        @error('price')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">説明(必要に応じて記入してください)</label>
                        </label>
                        <textarea name="description" id="description" rows="4"
                            class="form-select"value={{ $menu->description }}placeholder="{{ $menu->description }}"></textarea>
                        @error('description')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3 text-center">
                        <label for="image" class="form-label">画像</label>
                        <input type="file" name="image_file" id="image_file" class="form-control">
                        <div class="mt-3">
                            @if ($menu->image_file !== '')
                                <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image"
                                    style="max-width: 200px; height: auto;">
                            @else
                                <img src="{{ asset('storage/images/noimage.png') }}"
                                    style="max-width: 200px; height: auto;">
                            @endif
                        </div>

                        @error('image_file')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">ステータス</label>
                        <select name="status" id="status" class="form-select"value="{{ old('status') }}">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">在庫</label>
                        <input type="number" name="stock" id="stock" class="form-select" min="0"
                            value="{{ $menu->stock }}"placeholder="{{ $menu->stock }}">
                        @error('stock')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="is_new" class="form-label">新商品フラグ</label>
                        {{-- <input type="radio" id="is_new" name="is_new" > --}}
                        {{-- <label><input type="radio" name="is_new" value="1" {{ old('is_new', optional($menu)->is_new) == 1 ? 'checked' : '' }}> あり</label>
                <label><input type="radio" name="is_new" value="" {{ old('is_new', optional($menu)->is_new) == null ? 'checked' : '' }}> なし</label> --}}


                        <label><input type="radio" name="is_new" value="1"
                                {{ old('is_new', $menu->is_new) == 1 ? 'checked' : '' }}> あり</label>
                        <label><input type="radio" name="is_new" value=""
                                {{ old('is_new', $menu->is_new) == null ? 'checked' : '' }}> なし</label>

                        @error('status')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror

                    </div>

                    <div class="mb-3">
                        <label for="is_recommended" class="form-label">おすすめフラグ</label>
                        {{-- <input type="radio" id="is_recommended" name="is_recommended" > --}}
                        {{-- <label><input type="radio" name="is_recommended" value="1" {{ old('is_recommended', optional($menu)->is_recommended) == 1 ? 'checked' : '' }}> あり</label>
                <label><input type="radio" name="is_recommended" value="" {{ old('is_recommended', optional($menu)->is_recommended) == null ? 'checked' : '' }}> なし</label> --}}
                        <label><input type="radio" name="is_recommended" value="1"
                                {{ old('is_recommended', $menu->is_recommended) == 1 ? 'checked' : '' }}> あり</label>
                        <label><input type="radio" name="is_recommended" value=""
                                {{ old('is_recommended', $menu->is_recommended) == null ? 'checked' : '' }}> なし</label>
                        @error('status')
                            <p class="text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">メニュー編集</button>
                </form>
            </div>
        </div>
    </div>

    {{-- <form action="{{ route('admin.menus.update',$menu->id) }}" enctype="multipart/form-data" method="POST">
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

    {{-- 

    <div class="flex flex-col mt-4">
      <label for="status" class="text-gray-800">Status</label>
      <select name="status" id="status" class="border border-gray-200 px-4 py-2 mt-2"value="{{old('status')}}">
        <option value="active" {{ old('status', $menu->status) == 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ old('status', $menu->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
      </select>
      @error('status')
        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
      @enderror
    </div>

    <div class="flex flex-col mt-4">
      <label for="is_new" class="text-gray-800">新商品フラグ</label>
      {{-- <input type="radio" id="is_new" name="is_new" > --}}
    {{-- <label><input type="radio" name="is_new" value="1" {{ old('is_new', $menu->is_new) == 1 ? 'checked' : '' }}> あり</label>
      <label><input type="radio" name="is_new" value="" {{ old('is_new', $menu->is_new) == null ? 'checked' : '' }}> なし</label>
      
      @error('status')
          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
      @enderror
    </div> --}}
    {{-- 
    <div class="flex flex-col mt-4">
      <label for="is_recommended" class="text-gray-800">おすすめフラグ</label>
      {{-- <input type="radio" id="is_recommended" name="is_recommended" > --}}
    {{-- <label><input type="radio" name="is_recommended" value="1" {{ old('is_recommended', $menu->is_recommended) == 1 ? 'checked' : '' }}> あり</label>
      <label><input type="radio" name="is_recommended" value="" {{ old('is_recommended', $menu->is_recommended) == null ? 'checked' : '' }}> なし</label>
      @error('status')
          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
      @enderror
    </div> --}}

    {{-- <div>
      <strong>新商品フラグ:</strong>
      {{$menu->is_new ? 'あり' : 'なし'}} 
    </div>
    
    <div>
      <strong>おすすめフラグ:</strong>
      {{$menu->is_recommended ? 'あり' : 'なし'}} 
    {{-- </div> --}}

    {{-- <div class="mb-2">
      @if ($menu->image_file !== '')
          <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" style="max-width: 200px; height: auto;">
      @else
          <img src="{{ asset('storage/images/noimage.png') }}" style="max-width: 200px; height: auto;">
      @endif
    </div>
    <div class="flex flex-col mt-4">
      <label for="image" class="text-gray-800">Imageを変更する</label>
      <input type="file" name="image_file" id="image_file" class="border border-gray-200 px-4 py-2 mt-2">
      @error('image_file')
          <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
      @enderror
  </div>
    






    <div>
        <button type="submit">編集</button>
    </div>

  </form>  --}}
@endsection
