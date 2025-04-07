@extends('layouts.app')

@section('content')
<div class="container pt-5">
   <div class="row justify-content-center">
       <div class="col-md-5">
           <h1 class="text-center mb-3">決済は完了しました。</h3>

           <p class="text-center lh-lg mb-5">
               またのお越しをお待ちしております。<br>
           </p>

           <div class="text-center">
               <a href="{{ url('customer/login') }}" class="btn w-75 text-white">ログインへ</a>
           </div>
       </div>
   </div>
</div>
@endsection