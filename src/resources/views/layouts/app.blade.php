<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtechフリマ</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="logo">
            <a href="{{ route('home') }}">
                <img class="logo-img" src="{{ asset('images/icons/logo.svg') }}" alt="Coachtech Icon">
            </a>
        </div>
        @if (!Request::is('login') && !Request::is('register'))
            <form class="search" action="{{ route('home') }}" method="get">
                <input class="search-input" type="search" name="search" placeholder="       なにをお探しですか？" value="{{ $query ?? '' }}" />
                <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
            </form>
            <div class="nav">
                @if (Auth::check())
                    <form class="nav-logout" action="/logout" method="post">
                        @csrf
                        <button class="nav-logout__link" type="submit">ログアウト</button>
                    </form>
                @else
                    <a class="nav-mypage" href="/login">ログイン</a>
                @endif
                <a class="nav-mypage" href="/profile">マイページ</a>
                <a class="nav-sell__btn" href="{{ route('showSell') }}">出品</a>
            </div>
        @endif
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>