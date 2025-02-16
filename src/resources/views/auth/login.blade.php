@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <h2 class="login-title">ログイン</h2>
    <form class="login-form" action="/login" method="post">
        @csrf
        <div class="login-form__group">
            <label class="login-form__label" for="name">ユーザー名 / メールアドレス</label>
            <input class="login-form__input" type="email" id="email" name="email">
            @error('email')
                <p>{{ $message }}</p>
            @enderror
        </div>
        <div class="login-form__group">
            <label class="login-form__label" for="password">パスワード</label>
            <input class="login-form__input" type="password" id="password" name="password">
            @error('password')
                <p>{{ $message }}</p>
            @enderror
        </div>
        <button class="login-button" type="submit">ログインする</button>
    </form>
    <a class="register-link" href="/register">会員登録はこちら</a>
@endsection