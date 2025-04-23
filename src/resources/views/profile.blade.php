@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
    <div class="profile-container">
        <div class="user-data">
            @if (!empty($user->profile_image))
                <div class="profile-image">
                    <div class="img">
                        <img class="user-image" src="{{ asset('storage/' . $user->profile_image) }}" alt="プロフィール画像">
                    </div>
                </div>
            @endif
            <div class="user-name">{{ $user->name }}</div>
            <div class="profile-edit__link">
                <a class="edit-btn" href="{{ route('profile_edit') }}">プロフィールを編集</a>
            </div>
        </div>
        <div class="tab-menu">
            <div class="tab-box">
                <a href="{{ route('profile', ['tab' => 'selling']) }}" class="tab {{ $currentTab == 'selling' ? 'active' : '' }}">
                    出品した商品
                </a>
                <a href="{{ route('profile', ['tab' => 'purchased']) }}" class="tab {{ $currentTab == 'purchased' ? 'active' : '' }}">
                    購入した商品
                </a>
            </div>
        </div>
        <div class="item-list">
            @if ($currentTab == 'selling')
                @if ($sellingItems->isEmpty())
                    <p>現在、出品している商品はありません。</p>
                @else
                    <div class="item-list">
                        @foreach ($sellingItems as $item)
                            <div class="item">
                                @if ($item->sold)
                                    <div class="item-image">Sold</div>
                                @else
                                    <a href="{{ url('/item', $item->id) }}">
                                        <img class="item-image" src="{{ asset('storage/' . $item->item_image) }}" alt="商品画像" />
                                    </a>
                                @endif
                                <a class="item-name" href="{{ url('/item', $item->id) }}">
                                    <p>{{ $item->item_name }}</p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="pagination">
                        {{ $sellingItems->links() }}
                    </div>
                @endif
            @elseif ($currentTab == 'purchased')
                @if ($purchasedItems->isEmpty())
                    <p>現在、購入した商品はありません。</p>
                @else
                    <div class="item-list">
                        @foreach ($purchasedItems as $purchase)
                            <div class="item">
                                @if ($purchase->item->sold)
                                    <div class="item-image">Sold</div>
                                @else
                                    <a href="{{ url('/item', $purchase->item->id) }}">
                                        <img class="item-image" src="{{ asset('storage/' . $purchase->item->item_image) }}" alt="商品画像" />
                                    </a>
                                @endif
                                <a class="item-name" href="{{ url('/item', $purchase->item->id) }}">
                                    <p>{{ $purchase->item->item_name }}</p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="pagination">
                        {{ $purchasedItems->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection