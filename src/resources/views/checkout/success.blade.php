@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/success.css') }}">
@endsection

@section('content')
    <div class="container">
        <h1>ご購入ありがとうございました！</h1>
        @if ($isPending)
            <p>コンビニでのお支払いが確認できしだい、購入が完了します。</p>
            <p>お支払い期限を過ぎると、購入はキャンセルされます。</p>
        @else
            <p>購入が正常に完了しました。</p>
        @endif
        <a class="home-link" href="{{ url('/') }}">ホームに戻る</a>
    </div>
@endsection