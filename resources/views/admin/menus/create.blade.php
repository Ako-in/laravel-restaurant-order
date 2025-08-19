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
                <h4 class="mb-0">新規メニュー作成</h4>
            </div>
            <div class="mb-4"> {{-- 戻るボタンの親divに下マージン --}}
                <a href="{{ route('admin.menus.index') }}" class="btn btn-primary">戻る</a>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="{{ route('admin.menus.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex flex-col mt-4">
                        <label for="category_id" class="text-gray-800">カテゴリ：</label>
                        <select name="category_id" id="category_id" class="form-select mb-3">
                            <option value="">カテゴリを選択</option>
                            @foreach ($categories as $category)
                                {{-- <option value="{{ $category->id }}">{{ $category->name }}</option> --}}
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- 他のフィールド（name, price, descriptionなど） -->
                    <div class="mb-3">
                        <label for="name" class="form-label">名前</label>
                        <input type="text" name="name" id="name" class="form-select"value="{{ old('name') }}">
                        @error('name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">価格(税抜)</label>
                        <input type="number" name="price" id="price" class="form-select" value="{{ old('price') }}">
                        @error('price')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">説明(必要に応じて記入してください)</label>
                        </label>
                        <textarea name="description" id="description" rows="4" class="form-select"{{ old('description') }}></textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">画像</label>
                        <input type="file" name="image_file" id="image_file" class="form-select">
                        @error('image_file')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">ステータス</label>
                        <select name="status" id="status" class="form-select"value="{{ old('status') }}">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">在庫</label>
                        <input type="number" name="stock" id="stock" class="form-select" min="0"
                            value="{{ old('stock') }}">
                        @error('stock')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="is_new" class="form-label">新商品フラグ</label>
                        {{-- <input type="radio" id="is_new" name="is_new" > --}}
                        <label><input type="radio" name="is_new" value="1"
                                {{ old('is_new', optional($menu)->is_new) == 1 ? 'checked' : '' }}> あり</label>
                        <label><input type="radio" name="is_new" value=""
                                {{ old('is_new', optional($menu)->is_new) == null ? 'checked' : '' }}> なし</label>


                    </div>

                    <div class="mb-3">
                        <label for="is_recommended" class="form-label">おすすめフラグ</label>
                        {{-- <input type="radio" id="is_recommended" name="is_recommended" > --}}
                        <label><input type="radio" name="is_recommended" value="1"
                                {{ old('is_recommended', optional($menu)->is_recommended) == 1 ? 'checked' : '' }}>
                            あり</label>
                        <label><input type="radio" name="is_recommended" value=""
                                {{ old('is_recommended', optional($menu)->is_recommended) == null ? 'checked' : '' }}>
                            なし</label>
                        @error('status')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">新規メニュー作成</button>
                </form>
            </div>
        </div>
    </div>
@endsection
