@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/cancel.css') }}">
@endsection

@section('content')
    <div class="container">
        <h1>購入はキャンセルされました！</h1>
        <p>まだ完了していません。</p>
        <a class="home-link" href="{{ url('/') }}">商品をもう一度選ぶ</a>
    </div>
@endsection