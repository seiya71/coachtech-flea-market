@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/sell.css') }}">
@endsection

@section('content')
    <div class="sell-container">
        <h1 class="sell-title">商品の出品</h1>
        <form class="item-image" action="{{ route('uploadItemImage') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <p class="item-image__title">商品画像</p>
            <div class="item-image__upload">
                @if (session('item_image'))
                    <div class="preview-image">
                        <img src="{{ asset('storage/' . session('item_image')) }}" alt="商品画像">
                    </div>
                @endif
                <div class="form-group">
                    <label class="upload-button" for="item_image">画像を選択する</label>
                    <input class="img-label" type="file" name="item_image" id="item_image" accept="image/*"
                        onchange="this.form.submit()">
                </div>
            </div>

        </form>
        <form action="{{ route('sell') }}" method="POST">
            @csrf
            <div class="item-detail">
                <h2 class="item-detail__title">商品の詳細</h2>
                <div class="item-category">
                    <p class="category-title">カテゴリー</p>
                    <div class="category-value">
                        @foreach($categories as $category)
                                                <label class="category-button">
                                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ in_array(
                                $category->id,
                                old('categories', [])
                            ) ? 'checked' : '' }}>
                                                    <span>{{ $category->category_name }}</span>
                                                </label>
                        @endforeach
                    </div>
                </div>
                <div class="item-condition">
                    <p class="item-condition__title">商品の状態</p>
                    <select class="item-condition__select" name="condition" id="condition">
                        <option value="選択してください">選択してください</option>
                        <option value="良好">良好</option>
                        <option value="目立った傷や汚れなし">目立った傷や汚れなし</option>
                        <option value="やや傷や汚れあり">やや傷や汚れあり</option>
                        <option value="状態が悪い">状態が悪い</option>
                    </select>
                </div>
            </div>
            <div class="item-description">
                <h2 class="item-detail__title">商品名と説明</h2>
                <div class="form-group">
                    <label for="item-name">商品名</label>
                    <input type="text" id="item-name" name="item_name" required>
                    <label for="brand">ブランド</label>
                    <input type="text" id="brand" name="brand" required>
                </div>
                <div class="form-group">
                    <label for="description">商品の説明</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="price">販売価格</label>
                    <input type="text" id="price" name="price" placeholder="¥" required>
                </div>
            </div>
            <input type="hidden" name="item_image" value="{{ session('item_image') }}">
            <button type="submit" class="sell-btn">出品する</button>
        </form>
    </div>

@endsection