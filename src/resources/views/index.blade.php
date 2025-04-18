@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
    <ul class="tab-menu">
        <li class="tab"><a href="{{ url('/?tab=all') }}" class="tab-text {{ $tab == 'all' ? 'active' : '' }}">おすすめ</a></li>
        <li class="tab"><a href="{{ url('/?tab=mylist') }}"
                class="tab-text {{ $tab == 'mylist' ? 'active' : '' }}">マイリスト</a></li>
    </ul>
    <div class="item-box">
        @if ($tab == 'all')
            <div class="item-list">
                @foreach ($items as $item)
                    @if ($item)
                        <a class="item" href="{{ url('/item', $item->id) }}">
                            @if ($item->sold)
                                <div class="item-image">Sold</div>
                            @else
                                <img class="item-image" src="{{ asset('storage/' . $item->item_image) }}" alt="商品画像">
                            @endif
                            <p class="item-name">
                                {{ $item->item_name }}
                            </p>
                        </a>
                    @endif
                @endforeach
            </div>
            <div class="pagination">
                {{ $items->links() }}
            </div>
        @endif
        @if ($tab == 'mylist')
            @if ($myitems->isEmpty())
                <p>現在マイリストはありません。</p>
            @else
                <div class="item-list">
                    @foreach ($myitems as $item)
                        @if ($item)
                            <a class="item" href="{{ url('/item', $item->id) }}">
                                @if ($item->sold)
                                    <div class="item-image">Sold</div>
                                @else
                                    <img class="item-image" src="{{ asset('storage/' . $item->item_image) }}" alt="商品画像">
                                @endif
                                <p class="item-name">
                                    {{ $item->item_name }}
                                </p>
                            </a>
                        @endif
                    @endforeach
                </div>
                <div class="pagination">
                    {{ $myitems->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection