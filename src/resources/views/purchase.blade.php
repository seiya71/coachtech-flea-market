@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
    <div class="purchase-container">
        <div class="item-container">
            <div class="item-info">
                <img class="item-image" src="{{ asset('storage/' . $item->item_image) }}" alt="商品画像">
                <div class="item-data">
                    <h1 class="item-title">{{ $item->item_name }}</h1>
                    <div class="price">
                        <span class="price-symbol">￥</span>
                        <span class="price-amount">{{ number_format($item->price) }}</span>
                    </div>
                </div>
            </div>
            <div class="payment">
                <div class="payment-method">支払い方法</div>
                <form method="GET" action="{{ route('purchase', ['itemId' => $item->id]) }}">
                    <select class="payment-method__select" id="payment-method" name="payment_method" onchange="this.form.submit()">
                        <option value="" disabled {{ empty($selectedPaymentMethod) ? 'selected' : '' }}>選択してください</option>
                        @foreach ($paymentMethods as $key => $method)
                            <option value="{{ $key }}" {{ $selectedPaymentMethod === $key ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </form>

            </div>
            <div class="shipping-info">
                <div class="info-header">
                    <div class="address-title">配送先</div>
                    <a href="{{ route('address', ['itemId' => $item->id]) }}" class="address-edit__link">変更する</a>
                </div>
                <div class="address-data">
                    <p>〒 {{ $address->postal_code }}</p>
                    <p>{{ $address->address }}{{ $address->building_name }}</p>
                </div>
            </div>
        </div>
        <div class="summary-container">
            <table class="summary-table">
                <tr class="summary-table__row">
                    <td class="summary-table__label">商品代金</td>
                    <td class="summary-table__value">
                        <span>￥</span>
                        <span>{{ number_format($item->price) }}</span>
                    </td>
                </tr>
                <tr class="summary-table__row">
                    <td class="summary-table__label">支払い方法</td>
                    <td class="summary-table__value">{{ $paymentMethods[$selectedPaymentMethod] ?? '' }}</td>
                </tr>
            </table>
            <form class="button-box" method="POST" action="{{ route('checkout', ['itemId' => $item->id]) }}">
                @csrf
                <input type="hidden" name="item_id" value="{{ $item->id }}">
                <input type="hidden" name="payment_method" value="{{ $selectedPaymentMethod }}">
                <button class="purchase-btn" type="submit">購入する</button>
            </form>
        </div>
    </div>
@endsection