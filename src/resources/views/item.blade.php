@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/item.css') }}">
@endsection

@section('content')
    <div class="left">
        <img class="item-image" src="{{ asset('storage/' . $item->item_image) }}" alt="商品画像">
    </div>
    <div class="right">
        <section class="item-data">
            <h1 class="item-title">{{ $item->item_name }}</h1>
            <p class="brand-name">{{ $item->brand }}</p>
            <div class="price">
                <span class="price-amount">
                    <span class="currency">￥</span>
                    {{ number_format($item->price) }}
                    <span class="tax-inclusive">(税込)</span>
                </span>
            </div>
            <div class="actions">
                <div class="status">
                    @if($item->user_id != Auth::id())
                        <form action="{{ route('addlike', $item->id) }}" method="POST">
                            @csrf
                            @if($userHasLiked)
                                <button type="submit" class="status-img"
                                    style="background: none; border: none; cursor: pointer;">★</button>
                            @else
                                <button type="submit" class="status-img"
                                    style="background: none; border: none; cursor: pointer;">☆</button>
                            @endif
                        </form>
                    @else
                        <span class="status-img">★</span>
                    @endif
                    <div class="status-count">{{ $likeCount }}</div>
                </div>
                <div class="status">
                    <div class="status-img">💬</div>
                    <div class="status-count">{{ $commentCount }}</div>
                </div>
            </div>
            <form action="{{ url('/purchase', ['itemId' => $item->id])}}" method="GET">
                @csrf
                <button type="submit" class="buy-btn">購入手続きへ</button>
            </form>
        </section>

        <section class="item-description">
            <h2 class="item-heading">商品説明</h2>
            <div class="describe">
                {{ $item->description }}
            </div>
        </section>

        <section class="item-detail">
            <h2 class="item-heading">商品の情報</h2>
            <div class="item-info">
                <p class="item-info__list">カテゴリー</p>
                @foreach($item->categories as $category)
                    <span class="item-info__category">{{ $category->category_name }}</span>
                @endforeach
            </div>
            <div class="item-info">
                <p class="item-info__list">商品の状態</p>
                <span class="item-info__status">{{ $item->condition }}</span>
            </div>
        </section>

        <section class="comments">
            <h2 class="comments-heading">コメント（{{ $commentCount }}）</h2>
            @if ($commentCount > 0)
                <ul class="comment">
                    @foreach ($comments as $comment)
                        <li class="comment-item">
                            <div class="comment-user">
                                <img class="comment-user__img" src="{{ asset('storage/' . $comment['user_image']) }}" alt="ユーザー画像">
                                <p class="comment-user__name">{{ $comment['user_name'] }}</p>
                            </div>
                            <p class="comment-text">{{ $comment['content'] }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>こちらの商品にはまだコメントがありません</p>
            @endif
            <h2 class="edit-title">商品へのコメント</h2>
            <form action="{{ route('addcomment', $item->id) }}" method="POST">
                @csrf
                <textarea name="content"></textarea>
                <button class="comment-btn" type="submit">コメントを送信する</button>
            </form>
        </section>
    </div>
@endsection