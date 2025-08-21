@extends('layouts.admin')

@section('content')
    <div>
        @if($admin->email === 'guest@example.com')
            <div class="alert alert-warning text-center rounded-0 mb-0 py-2 pt-3" role="alert">
                <strong>💡 このアカウントはデモ用です。</strong> データの変更などはできません。
            </div>
        @endif
    </div>
    <div class="col container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11">
                <div class="row row-cols-md-1 row-cols-2 g-3 mb-5 mt-3">
                    <div class="col">
                        <a href="{{ route('admin.orders.index') }}" class="card-link-button text-decoration-none">
                            <div class="card bg-light shadow-sm h-100 d-flex align-items-center justify-content-center">
                                <div class="card-body text-center w-100">
                                    <h5 class="card-title text-dark mb-0">注文一覧</h5>
                                </div>
                            </div>
                        </a>

                    </div>
                    <div class="col">
                        <a href="{{ route('admin.menus.index') }}" class="card-link-button text-decoration-none">
                            <div class="card bg-light shadow-sm h-100 d-flex align-items-center justify-content-center">
                                <div class="card-body text-center w-100">
                                    <h5 class="card-title text-dark mb-0">在庫一覧</h5>
                                </div>
                            </div>
                        </a>

                    </div>
                    <div class="col">
                        <a href="{{ route('admin.menus.create') }}" class="card-link-button text-decoration-none">
                            <div class="card bg-light shadow-sm h-100 d-flex align-items-center justify-content-center">
                                <div class="card-body text-center w-100">
                                    <h5 class="card-title text-dark mb-0">新規メニュー作成</h5>
                                </div>
                            </div>
                        </a>

                    </div>
                    <div class="col">
                        <a href="{{ route('admin.sales.chart') }}" class="card-link-button text-decoration-none">
                            <div class="card bg-light shadow-sm h-100 d-flex align-items-center justify-content-center">
                                <div class="card-body text-center w-100">
                                    <h5 class="card-title text-dark mb-0 ">売上実績</h5>
                                </div>
                            </div>
                        </a>

                    </div>

                    <div class="col">
                        <a href="{{ route('admin.sales_target.index') }}" class="card-link-button text-decoration-none">
                            <div class="card bg-light shadow-sm h-100 d-flex align-items-center justify-content-center">
                                <div class="card-body text-center w-100">
                                    <h5 class="card-title text-dark mb-0 ">売上目標</h5>
                                </div>
                            </div>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
