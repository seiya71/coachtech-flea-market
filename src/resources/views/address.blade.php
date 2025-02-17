@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
    <h2 class="address-title">住所の変更</h2>
    <form class="address-form" method="POST" action="{{ route('addressEdit', ['itemId' => $item->id]) }}">
        @csrf
        <div class="address-form__group">
            <label class="address-form__label" for="postal_code">郵便番号</label>
            <input class="address-form__input" type="text" name="postal_code"
                value="{{ old('postal_code', $user->postal_code) }}" required>
        </div>
        <div class="address-form__group">
            <label class="address-form__label" for="address">住所</label>
            <input class="address-form__input" type="text" name="address" value="{{ old('address', $user->address) }}"
                required>
        </div>
        <div class="address-form__group">
            <label class="address-form__label" for="building_name">建物名</label>
            <input class="address-form__input" type="text" name="building_name"
                value="{{ old('building_name', $user->building_name) }}">
        </div>
        <button class="address-button" type="submit">更新する</button>
    </form>
@endsection