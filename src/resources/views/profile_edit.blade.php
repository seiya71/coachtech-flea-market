@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile_edit.css') }}">
@endsection

@section('content')
    <div class="profile-edit__container">
        <h2 class="address-title">プロフィール設定</h2>

        <form class="img-form" action="{{ route('updateUserImage') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="profile-image">
                <div class="img">
                    <img src="{{ session('profile_image') ? asset('storage/' . session('profile_image')) : asset('storage/' . Auth::user()->profile_image) }}"
                        alt="Profile Image">

                </div>
            </div>
            <label class="img-button" for="profile_image" class="img-button">画像を選択する</label>
            <input class="img-label" type="file" name="profile_image" id="profile_image" accept="image/*"
                onchange="this.form.submit()">
        </form>

        <form class="address-form" action="{{ route('edit') }}" method="POST">
            @csrf
            <div class="address-form__group">
                <label class="address-form__label" for="name">ユーザー名</label>
                <input class="address-form__input" type="text" name="name" value="{{ old('name', $user->name) }}">
            </div>

            <div class="address-form__group">
                <label class="address-form__label" for="postal_code">郵便番号</label>
                <input class="address-form__input" type="text" name="postal_code"
                    value="{{ old('postal_code', $user->postal_code) }}">
            </div>

            <div class="address-form__group">
                <label class="address-form__label" for="address">住所</label>
                <input class="address-form__input" type="text" name="address" value="{{ old('address', $user->address) }}">
            </div>

            <div class="address-form__group">
                <label class="address-form__label" for="building_name">建物名</label>
                <input class="address-form__input" type="text" name="building_name"
                    value="{{ old('building_name', $user->building_name) }}">
            </div>

            <button class="address-button" type="submit">情報を更新する</button>
        </form>
    </div>
@endsection