@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/success.css') }}">
@endsection

@section('content')
    <div class="container">
        <h1>ご購入ありがとうございました！</h1>
        <p>購入が正常に完了しました。</p>
        <a class="home-link" href="{{ url('/') }}">ホームに戻る</a>
    </div>
@endsection