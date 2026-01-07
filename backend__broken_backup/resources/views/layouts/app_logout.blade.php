<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>トップページ</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a href="/">
            <img class="company" src="/image_icon/logo.svg" alt="会社名">
            </a>
        <form action="{{ route('front_page') }}" method="get">
            <input type = "text" class="search_form" name="all_item_search" placeholder="　なにをお探しですか？">
        </form>
            @if (Auth::check())
                <div class="login_page0">


        <form action="/logout" method="post">
            @csrf
            <input type="submit" class="login_page_1" value="ログアウト">
        </form>

        <form action="/mypage?page=sell" method="get">
            @csrf
            <input type="submit" class="login_page_2" value="マイページ">
        </form>

        <form action="/sell" method="get">
            @csrf
            <input type="submit" class="login_page_3" value="出品">
        </form>
                </div>
        </div>


            @else
                <div class="login_page0">
                    <a class="login_page_1" href="{{ route('login') }}">ログイン</a>
                    <a class="login_page_2" href="{{ route('login') }}">マイページ</a>
                    <a class="login_page_3" href="{{ route('login') }}">出品</a>
                </div>
            @endif
    </header>

    <main>
    @yield('content')
    </main>

</body>

</html>