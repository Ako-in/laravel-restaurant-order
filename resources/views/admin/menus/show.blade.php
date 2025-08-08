@extends('layouts.admin')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-1"> メニュー詳細</h2>
        </div>
        <div>
            <a class="btn btn-secondary"href="{{ route('admin.menus.index') }}"> Back</a>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8"> {{-- 中画面以上で8列分の幅を取り、中央寄せ --}}

                {{-- 画像表示部分 --}}
                <div class="text-center mb-4"> {{-- 画像を中央寄せし、下部にマージン --}}
                    @if ($menu->image_file)
                        {{-- $menu->image_file が存在するかをチェック --}}
                        <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="img-fluid rounded"
                            style="max-width: 300px; height: auto;">
                    @else
                        <img src="{{ asset('storage/images/noimage.png') }}" alt="No Image" class="img-fluid rounded"
                            style="max-width: 300px; height: auto;">
                    @endif
                </div>

                {{-- 各詳細項目 --}}
                <div class="card mb-4"> {{-- カードで囲んで見やすくする (オプション) --}}
                    <div class="card-body">
                        <h5 class="card-title mb-3">メニュー情報</h5>
                        <dl class="row"> {{-- 定義リストを使って項目と値を整形 --}}
                            <dt class="col-sm-4">ID:</dt>
                            <dd class="col-sm-8">{{ $menu->id }}</dd>

                            <dt class="col-sm-4">名前:</dt>
                            <dd class="col-sm-8">{{ $menu->name }}</dd>

                            <dt class="col-sm-4">カテゴリ:</dt>
                            <dd class="col-sm-8">{{ $menu->category->name ?? 'N/A' }}</dd> {{-- カテゴリ名を表示し、nullチェックも追加 --}}

                            <dt class="col-sm-4">説明:</dt>
                            <dd class="col-sm-8">{{ $menu->description }}</dd>

                            <dt class="col-sm-4">ステータス:</dt>
                            <dd class="col-sm-8">{{ $menu->status == 'active' ? '公開中' : '非公開' }}</dd> {{-- より分かりやすい表示に --}}

                            <dt class="col-sm-4">在庫:</dt>
                            <dd class="col-sm-8">{{ $menu->stock }}</dd>

                            <dt class="col-sm-4">価格:</dt>
                            <dd class="col-sm-8">{{ number_format($menu->price) }}円</dd> {{-- 価格を見やすくフォーマット --}}

                            <dt class="col-sm-4">新商品フラグ:</dt>
                            <dd class="col-sm-8">{{ $menu->is_new ? 'あり (⚪︎)' : 'なし (-)' }}</dd>

                            <dt class="col-sm-4">おすすめフラグ:</dt>
                            <dd class="col-sm-8">{{ $menu->is_recommended ? 'あり (⚪︎)' : 'なし (-)' }}</dd>

                            <dt class="col-sm-4">登録日:</dt>
                            <dd class="col-sm-8">{{ $menu->created_at->format('Y/m/d H:i') }}</dd> {{-- 日付をフォーマット --}}

                            <dt class="col-sm-4">更新日:</dt>
                            <dd class="col-sm-8">{{ $menu->updated_at->format('Y/m/d H:i') }}</dd> {{-- 日付をフォーマット --}}
                        </dl>
                    </div>
                </div>

                {{-- 編集画面へのリンク --}}
                <div class="text-center mt-4">
                    <a class="btn btn-primary" href="{{ route('admin.menus.edit', $menu->id) }}">編集画面へ</a>
                </div>

            </div>
        </div>
    </div>
@endsection
